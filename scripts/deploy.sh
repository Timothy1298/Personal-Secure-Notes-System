#!/bin/bash

# Personal Notes System - Deployment Script
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="personal-notes"
NAMESPACE="personal-notes"
AWS_REGION="us-west-2"
AWS_ACCOUNT_ID=""
ENVIRONMENT="production"

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_prerequisites() {
    log_info "Checking prerequisites..."
    
    # Check if required tools are installed
    local tools=("docker" "kubectl" "helm" "terraform" "aws")
    for tool in "${tools[@]}"; do
        if ! command -v "$tool" &> /dev/null; then
            log_error "$tool is not installed. Please install it first."
            exit 1
        fi
    done
    
    # Check AWS credentials
    if ! aws sts get-caller-identity &> /dev/null; then
        log_error "AWS credentials not configured. Please run 'aws configure' first."
        exit 1
    fi
    
    # Get AWS Account ID
    AWS_ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text)
    log_info "Using AWS Account ID: $AWS_ACCOUNT_ID"
    
    # Check kubectl context
    if ! kubectl cluster-info &> /dev/null; then
        log_error "kubectl is not connected to a cluster. Please configure your kubeconfig."
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

build_and_push_image() {
    log_info "Building and pushing Docker image..."
    
    # Build the image
    docker build -t $PROJECT_NAME:latest .
    
    # Tag for ECR
    docker tag $PROJECT_NAME:latest $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$PROJECT_NAME:latest
    
    # Login to ECR
    aws ecr get-login-password --region $AWS_REGION | docker login --username AWS --password-stdin $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com
    
    # Push to ECR
    docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$PROJECT_NAME:latest
    
    log_success "Docker image built and pushed successfully"
}

deploy_infrastructure() {
    log_info "Deploying infrastructure with Terraform..."
    
    cd terraform
    
    # Initialize Terraform
    terraform init
    
    # Plan the deployment
    terraform plan -var="aws_account_id=$AWS_ACCOUNT_ID" -var="environment=$ENVIRONMENT"
    
    # Apply the deployment
    terraform apply -auto-approve -var="aws_account_id=$AWS_ACCOUNT_ID" -var="environment=$ENVIRONMENT"
    
    cd ..
    
    log_success "Infrastructure deployed successfully"
}

deploy_kubernetes() {
    log_info "Deploying Kubernetes resources..."
    
    # Create namespace
    kubectl apply -f kubernetes/namespace.yaml
    
    # Apply secrets
    kubectl apply -f kubernetes/secrets.yaml
    
    # Apply configmaps
    kubectl apply -f kubernetes/configmap.yaml
    
    # Deploy MySQL
    kubectl apply -f kubernetes/mysql.yaml
    
    # Wait for MySQL to be ready
    log_info "Waiting for MySQL to be ready..."
    kubectl wait --for=condition=ready pod -l app=mysql -n $NAMESPACE --timeout=300s
    
    # Deploy Redis
    kubectl apply -f kubernetes/redis.yaml
    
    # Wait for Redis to be ready
    log_info "Waiting for Redis to be ready..."
    kubectl wait --for=condition=ready pod -l app=redis -n $NAMESPACE --timeout=300s
    
    # Deploy application
    kubectl apply -f kubernetes/app.yaml
    
    # Wait for application to be ready
    log_info "Waiting for application to be ready..."
    kubectl wait --for=condition=ready pod -l app=personal-notes-app -n $NAMESPACE --timeout=300s
    
    # Deploy ingress
    kubectl apply -f kubernetes/ingress.yaml
    
    # Deploy HPA
    kubectl apply -f kubernetes/hpa.yaml
    
    # Deploy monitoring
    kubectl apply -f kubernetes/monitoring.yaml
    
    log_success "Kubernetes resources deployed successfully"
}

run_migrations() {
    log_info "Running database migrations..."
    
    # Get a pod name
    local pod_name=$(kubectl get pods -n $NAMESPACE -l app=personal-notes-app -o jsonpath='{.items[0].metadata.name}')
    
    # Run migrations
    kubectl exec -n $NAMESPACE $pod_name -- php create_missing_tables.php
    
    log_success "Database migrations completed"
}

setup_monitoring() {
    log_info "Setting up monitoring..."
    
    # Install Prometheus Operator (if not already installed)
    if ! kubectl get crd prometheuses.monitoring.coreos.com &> /dev/null; then
        log_info "Installing Prometheus Operator..."
        helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
        helm repo update
        helm install prometheus prometheus-community/kube-prometheus-stack \
            --namespace monitoring \
            --create-namespace \
            --set grafana.adminPassword=admin
    fi
    
    # Install NGINX Ingress Controller (if not already installed)
    if ! kubectl get pods -n ingress-nginx &> /dev/null; then
        log_info "Installing NGINX Ingress Controller..."
        kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/controller-v1.8.1/deploy/static/provider/cloud/deploy.yaml
    fi
    
    log_success "Monitoring setup completed"
}

verify_deployment() {
    log_info "Verifying deployment..."
    
    # Check if all pods are running
    local pods_not_ready=$(kubectl get pods -n $NAMESPACE --field-selector=status.phase!=Running --no-headers | wc -l)
    if [ $pods_not_ready -gt 0 ]; then
        log_warning "Some pods are not running:"
        kubectl get pods -n $NAMESPACE --field-selector=status.phase!=Running
    else
        log_success "All pods are running"
    fi
    
    # Check services
    kubectl get services -n $NAMESPACE
    
    # Check ingress
    kubectl get ingress -n $NAMESPACE
    
    # Get application URL
    local app_url=$(kubectl get ingress -n $NAMESPACE -o jsonpath='{.items[0].spec.rules[0].host}')
    if [ -n "$app_url" ]; then
        log_success "Application is available at: https://$app_url"
    fi
    
    # Health check
    local pod_name=$(kubectl get pods -n $NAMESPACE -l app=personal-notes-app -o jsonpath='{.items[0].metadata.name}')
    if kubectl exec -n $NAMESPACE $pod_name -- curl -f http://localhost/health &> /dev/null; then
        log_success "Application health check passed"
    else
        log_warning "Application health check failed"
    fi
}

cleanup() {
    log_info "Cleaning up resources..."
    
    # Delete Kubernetes resources
    kubectl delete namespace $NAMESPACE --ignore-not-found=true
    
    # Delete Terraform resources
    cd terraform
    terraform destroy -auto-approve -var="aws_account_id=$AWS_ACCOUNT_ID" -var="environment=$ENVIRONMENT"
    cd ..
    
    log_success "Cleanup completed"
}

show_help() {
    echo "Personal Notes System Deployment Script"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  deploy     Deploy the entire application"
    echo "  build      Build and push Docker image"
    echo "  infra      Deploy infrastructure only"
    echo "  k8s        Deploy Kubernetes resources only"
    echo "  migrate    Run database migrations"
    echo "  monitor    Setup monitoring"
    echo "  verify     Verify deployment"
    echo "  cleanup    Clean up all resources"
    echo "  help       Show this help message"
    echo ""
    echo "Environment Variables:"
    echo "  AWS_REGION     AWS region (default: us-west-2)"
    echo "  ENVIRONMENT    Environment name (default: production)"
    echo "  PROJECT_NAME   Project name (default: personal-notes)"
}

main() {
    case "${1:-deploy}" in
        "deploy")
            check_prerequisites
            build_and_push_image
            deploy_infrastructure
            deploy_kubernetes
            run_migrations
            setup_monitoring
            verify_deployment
            ;;
        "build")
            check_prerequisites
            build_and_push_image
            ;;
        "infra")
            check_prerequisites
            deploy_infrastructure
            ;;
        "k8s")
            check_prerequisites
            deploy_kubernetes
            ;;
        "migrate")
            run_migrations
            ;;
        "monitor")
            setup_monitoring
            ;;
        "verify")
            verify_deployment
            ;;
        "cleanup")
            cleanup
            ;;
        "help"|"-h"|"--help")
            show_help
            ;;
        *)
            log_error "Unknown command: $1"
            show_help
            exit 1
            ;;
    esac
}

# Run main function with all arguments
main "$@"
