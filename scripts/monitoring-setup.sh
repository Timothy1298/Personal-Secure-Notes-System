#!/bin/bash

# Personal Notes System - Monitoring Setup Script
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
NAMESPACE="personal-notes"
MONITORING_NAMESPACE="monitoring"

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
    
    # Check if kubectl is available
    if ! command -v kubectl &> /dev/null; then
        log_error "kubectl is not installed. Please install it first."
        exit 1
    fi
    
    # Check if helm is available
    if ! command -v helm &> /dev/null; then
        log_error "helm is not installed. Please install it first."
        exit 1
    fi
    
    # Check kubectl context
    if ! kubectl cluster-info &> /dev/null; then
        log_error "kubectl is not connected to a cluster. Please configure your kubeconfig."
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

install_prometheus_operator() {
    log_info "Installing Prometheus Operator..."
    
    # Add Prometheus Community Helm repository
    helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
    helm repo update
    
    # Create monitoring namespace
    kubectl create namespace $MONITORING_NAMESPACE --dry-run=client -o yaml | kubectl apply -f -
    
    # Install Prometheus Operator
    helm upgrade --install prometheus prometheus-community/kube-prometheus-stack \
        --namespace $MONITORING_NAMESPACE \
        --set grafana.adminPassword=admin \
        --set prometheus.prometheusSpec.retention=30d \
        --set prometheus.prometheusSpec.storageSpec.volumeClaimTemplate.spec.resources.requests.storage=50Gi \
        --set alertmanager.alertmanagerSpec.storage.volumeClaimTemplate.spec.resources.requests.storage=10Gi \
        --set grafana.persistence.enabled=true \
        --set grafana.persistence.size=10Gi \
        --wait
    
    log_success "Prometheus Operator installed successfully"
}

install_nginx_ingress() {
    log_info "Installing NGINX Ingress Controller..."
    
    # Add NGINX Ingress Helm repository
    helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
    helm repo update
    
    # Install NGINX Ingress Controller
    helm upgrade --install ingress-nginx ingress-nginx/ingress-nginx \
        --namespace ingress-nginx \
        --create-namespace \
        --set controller.service.type=LoadBalancer \
        --set controller.metrics.enabled=true \
        --set controller.podAnnotations."prometheus\.io/scrape"="true" \
        --set controller.podAnnotations."prometheus\.io/port"="10254" \
        --wait
    
    log_success "NGINX Ingress Controller installed successfully"
}

install_cert_manager() {
    log_info "Installing cert-manager..."
    
    # Add cert-manager Helm repository
    helm repo add jetstack https://charts.jetstack.io
    helm repo update
    
    # Install cert-manager
    helm upgrade --install cert-manager jetstack/cert-manager \
        --namespace cert-manager \
        --create-namespace \
        --version v1.13.0 \
        --set installCRDs=true \
        --wait
    
    # Create Let's Encrypt ClusterIssuer
    kubectl apply -f - <<EOF
apiVersion: cert-manager.io/v1
kind: ClusterIssuer
metadata:
  name: letsencrypt-prod
spec:
  acme:
    server: https://acme-v02.api.letsencrypt.org/directory
    email: admin@example.com
    privateKeySecretRef:
      name: letsencrypt-prod
    solvers:
    - http01:
        ingress:
          class: nginx
EOF
    
    log_success "cert-manager installed successfully"
}

setup_application_monitoring() {
    log_info "Setting up application monitoring..."
    
    # Create ServiceMonitor for the application
    kubectl apply -f - <<EOF
apiVersion: monitoring.coreos.com/v1
kind: ServiceMonitor
metadata:
  name: personal-notes-monitor
  namespace: $NAMESPACE
  labels:
    app: personal-notes-app
spec:
  selector:
    matchLabels:
      app: personal-notes-app
  endpoints:
  - port: http
    path: /metrics
    interval: 30s
EOF
    
    # Create PrometheusRule for custom alerts
    kubectl apply -f - <<EOF
apiVersion: monitoring.coreos.com/v1
kind: PrometheusRule
metadata:
  name: personal-notes-alerts
  namespace: $NAMESPACE
  labels:
    app: personal-notes-app
spec:
  groups:
  - name: personal-notes.rules
    rules:
    - alert: PersonalNotesHighErrorRate
      expr: rate(http_requests_total{status=~"5.."}[5m]) / rate(http_requests_total[5m]) > 0.1
      for: 5m
      labels:
        severity: warning
      annotations:
        summary: "High error rate detected in Personal Notes System"
        description: "Error rate is above 10% for more than 5 minutes"
    
    - alert: PersonalNotesSlowResponse
      expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 2
      for: 5m
      labels:
        severity: warning
      annotations:
        summary: "Slow response time detected in Personal Notes System"
        description: "95th percentile response time is above 2 seconds"
    
    - alert: PersonalNotesDatabaseConnectionFailure
      expr: mysql_up == 0
      for: 1m
      labels:
        severity: critical
      annotations:
        summary: "Database connection failed"
        description: "Cannot connect to MySQL database"
    
    - alert: PersonalNotesRedisConnectionFailure
      expr: redis_up == 0
      for: 1m
      labels:
        severity: critical
      annotations:
        summary: "Redis connection failed"
        description: "Cannot connect to Redis cache"
EOF
    
    log_success "Application monitoring configured successfully"
}

setup_grafana_dashboards() {
    log_info "Setting up Grafana dashboards..."
    
    # Create Grafana dashboard for Personal Notes System
    kubectl apply -f - <<EOF
apiVersion: v1
kind: ConfigMap
metadata:
  name: personal-notes-dashboard
  namespace: $MONITORING_NAMESPACE
  labels:
    grafana_dashboard: "1"
data:
  personal-notes.json: |
    {
      "dashboard": {
        "id": null,
        "title": "Personal Notes System",
        "tags": ["personal-notes"],
        "style": "dark",
        "timezone": "browser",
        "panels": [
          {
            "id": 1,
            "title": "Request Rate",
            "type": "graph",
            "targets": [
              {
                "expr": "rate(http_requests_total[5m])",
                "legendFormat": "{{method}} {{endpoint}}"
              }
            ],
            "yAxes": [
              {
                "label": "requests/sec"
              }
            ]
          },
          {
            "id": 2,
            "title": "Response Time",
            "type": "graph",
            "targets": [
              {
                "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))",
                "legendFormat": "95th percentile"
              },
              {
                "expr": "histogram_quantile(0.50, rate(http_request_duration_seconds_bucket[5m]))",
                "legendFormat": "50th percentile"
              }
            ],
            "yAxes": [
              {
                "label": "seconds"
              }
            ]
          },
          {
            "id": 3,
            "title": "Error Rate",
            "type": "graph",
            "targets": [
              {
                "expr": "rate(http_requests_total{status=~\"5..\"}[5m]) / rate(http_requests_total[5m]) * 100",
                "legendFormat": "Error Rate %"
              }
            ],
            "yAxes": [
              {
                "label": "percent"
              }
            ]
          },
          {
            "id": 4,
            "title": "Active Users",
            "type": "singlestat",
            "targets": [
              {
                "expr": "mysql_global_status_threads_connected",
                "legendFormat": "Active Connections"
              }
            ]
          }
        ],
        "time": {
          "from": "now-1h",
          "to": "now"
        },
        "refresh": "30s"
      }
    }
EOF
    
    log_success "Grafana dashboards configured successfully"
}

setup_logging() {
    log_info "Setting up centralized logging..."
    
    # Install Elasticsearch
    helm repo add elastic https://helm.elastic.co
    helm repo update
    
    helm upgrade --install elasticsearch elastic/elasticsearch \
        --namespace $MONITORING_NAMESPACE \
        --set replicas=1 \
        --set volumeClaimTemplate.resources.requests.storage=10Gi \
        --wait
    
    # Install Kibana
    helm upgrade --install kibana elastic/kibana \
        --namespace $MONITORING_NAMESPACE \
        --set elasticsearchHosts="http://elasticsearch-master:9200" \
        --wait
    
    # Install Fluentd
    kubectl apply -f - <<EOF
apiVersion: v1
kind: ConfigMap
metadata:
  name: fluentd-config
  namespace: $MONITORING_NAMESPACE
data:
  fluent.conf: |
    <source>
      @type tail
      path /var/log/containers/*personal-notes*.log
      pos_file /var/log/fluentd-containers.log.pos
      tag kubernetes.*
      format json
      time_key time
      time_format %Y-%m-%dT%H:%M:%S.%NZ
    </source>
    
    <match kubernetes.**>
      @type elasticsearch
      host elasticsearch-master
      port 9200
      index_name personal-notes-logs
      type_name _doc
    </match>
EOF
    
    # Deploy Fluentd DaemonSet
    kubectl apply -f - <<EOF
apiVersion: apps/v1
kind: DaemonSet
metadata:
  name: fluentd
  namespace: $MONITORING_NAMESPACE
spec:
  selector:
    matchLabels:
      name: fluentd
  template:
    metadata:
      labels:
        name: fluentd
    spec:
      containers:
      - name: fluentd
        image: fluent/fluentd-kubernetes-daemonset:v1-debian-elasticsearch
        env:
        - name: FLUENT_ELASTICSEARCH_HOST
          value: "elasticsearch-master"
        - name: FLUENT_ELASTICSEARCH_PORT
          value: "9200"
        volumeMounts:
        - name: varlog
          mountPath: /var/log
        - name: varlibdockercontainers
          mountPath: /var/lib/docker/containers
          readOnly: true
      volumes:
      - name: varlog
        hostPath:
          path: /var/log
      - name: varlibdockercontainers
        hostPath:
          path: /var/lib/docker/containers
EOF
    
    log_success "Centralized logging configured successfully"
}

verify_monitoring() {
    log_info "Verifying monitoring setup..."
    
    # Check Prometheus
    if kubectl get pods -n $MONITORING_NAMESPACE -l app.kubernetes.io/name=prometheus | grep -q Running; then
        log_success "Prometheus is running"
    else
        log_warning "Prometheus is not running"
    fi
    
    # Check Grafana
    if kubectl get pods -n $MONITORING_NAMESPACE -l app.kubernetes.io/name=grafana | grep -q Running; then
        log_success "Grafana is running"
    else
        log_warning "Grafana is not running"
    fi
    
    # Check AlertManager
    if kubectl get pods -n $MONITORING_NAMESPACE -l app.kubernetes.io/name=alertmanager | grep -q Running; then
        log_success "AlertManager is running"
    else
        log_warning "AlertManager is not running"
    fi
    
    # Get service URLs
    log_info "Monitoring services:"
    kubectl get services -n $MONITORING_NAMESPACE
    
    # Port forward for local access (optional)
    log_info "To access Grafana locally, run:"
    echo "kubectl port-forward -n $MONITORING_NAMESPACE svc/prometheus-grafana 3000:80"
    echo "Username: admin, Password: admin"
    
    log_info "To access Prometheus locally, run:"
    echo "kubectl port-forward -n $MONITORING_NAMESPACE svc/prometheus-kube-prometheus-prometheus 9090:9090"
}

show_help() {
    echo "Personal Notes System - Monitoring Setup Script"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  setup      Setup complete monitoring stack"
    echo "  prometheus Install Prometheus Operator only"
    echo "  ingress    Install NGINX Ingress Controller only"
    echo "  certs      Install cert-manager only"
    echo "  app        Setup application monitoring only"
    echo "  dashboards Setup Grafana dashboards only"
    echo "  logging    Setup centralized logging only"
    echo "  verify     Verify monitoring setup"
    echo "  help       Show this help message"
    echo ""
}

main() {
    case "${1:-setup}" in
        "setup")
            check_prerequisites
            install_prometheus_operator
            install_nginx_ingress
            install_cert_manager
            setup_application_monitoring
            setup_grafana_dashboards
            setup_logging
            verify_monitoring
            ;;
        "prometheus")
            check_prerequisites
            install_prometheus_operator
            ;;
        "ingress")
            check_prerequisites
            install_nginx_ingress
            ;;
        "certs")
            check_prerequisites
            install_cert_manager
            ;;
        "app")
            setup_application_monitoring
            ;;
        "dashboards")
            setup_grafana_dashboards
            ;;
        "logging")
            setup_logging
            ;;
        "verify")
            verify_monitoring
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
