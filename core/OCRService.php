<?php
namespace Core;

use PDO;
use Exception;

class OCRService {
    private $db;
    private $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'pdf'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Process image for OCR
     */
    public function processImage($userId, $imageData, $filename) {
        try {
            // Validate image
            $validation = $this->validateImage($imageData, $filename);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }
            
            // Save image
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $uniqueFilename = uniqid() . '_' . time() . '.' . $extension;
            $uploadPath = __DIR__ . '/../../uploads/ocr_images/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $filePath = $uploadPath . $uniqueFilename;
            if (!file_put_contents($filePath, $imageData)) {
                return ['success' => false, 'error' => 'Failed to save image'];
            }
            
            // Perform OCR
            $ocrResult = $this->performOCR($filePath);
            
            // Save OCR result to database
            $stmt = $this->db->prepare("
                INSERT INTO ocr_results (user_id, filename, original_filename, file_path, extracted_text, confidence, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $uniqueFilename,
                $filename,
                $filePath,
                $ocrResult['text'],
                $ocrResult['confidence']
            ]);
            
            $ocrId = $this->db->lastInsertId();
            
            return [
                'success' => true,
                'ocr_id' => $ocrId,
                'extracted_text' => $ocrResult['text'],
                'confidence' => $ocrResult['confidence'],
                'filename' => $uniqueFilename
            ];
            
        } catch (Exception $e) {
            error_log("OCR processing failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'OCR processing failed'];
        }
    }
    
    /**
     * Perform OCR on image
     */
    private function performOCR($imagePath) {
        // Try different OCR methods
        $methods = [
            'tesseract' => [$this, 'tesseractOCR'],
            'google_vision' => [$this, 'googleVisionOCR'],
            'azure_vision' => [$this, 'azureVisionOCR']
        ];
        
        foreach ($methods as $method => $callback) {
            try {
                $result = $callback($imagePath);
                if ($result && !empty($result['text'])) {
                    return $result;
                }
            } catch (Exception $e) {
                error_log("OCR method {$method} failed: " . $e->getMessage());
                continue;
            }
        }
        
        // Fallback to basic image analysis
        return $this->basicImageAnalysis($imagePath);
    }
    
    /**
     * Tesseract OCR
     */
    private function tesseractOCR($imagePath) {
        if (!function_exists('exec')) {
            throw new Exception('exec function not available');
        }
        
        // Check if tesseract is installed
        exec('which tesseract', $output, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception('Tesseract not installed');
        }
        
        $tempFile = tempnam(sys_get_temp_dir(), 'ocr_');
        $tempFile .= '.txt';
        
        // Run tesseract
        $command = "tesseract " . escapeshellarg($imagePath) . " " . escapeshellarg($tempFile) . " 2>/dev/null";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Tesseract execution failed');
        }
        
        if (!file_exists($tempFile)) {
            throw new Exception('Tesseract output file not found');
        }
        
        $text = file_get_contents($tempFile);
        unlink($tempFile);
        
        return [
            'text' => trim($text),
            'confidence' => 0.8 // Tesseract doesn't provide confidence by default
        ];
    }
    
    /**
     * Google Vision API OCR
     */
    private function googleVisionOCR($imagePath) {
        $apiKey = $_ENV['GOOGLE_VISION_API_KEY'] ?? null;
        if (!$apiKey) {
            throw new Exception('Google Vision API key not configured');
        }
        
        $imageData = base64_encode(file_get_contents($imagePath));
        
        $requestData = [
            'requests' => [
                [
                    'image' => [
                        'content' => $imageData
                    ],
                    'features' => [
                        [
                            'type' => 'TEXT_DETECTION',
                            'maxResults' => 1
                        ]
                    ]
                ]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Google Vision API request failed');
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['responses'][0]['textAnnotations'][0]['description'])) {
            $text = $data['responses'][0]['textAnnotations'][0]['description'];
            $confidence = 0.9; // Google Vision typically has high confidence
            
            return [
                'text' => trim($text),
                'confidence' => $confidence
            ];
        }
        
        throw new Exception('No text found in image');
    }
    
    /**
     * Azure Vision API OCR
     */
    private function azureVisionOCR($imagePath) {
        $endpoint = $_ENV['AZURE_VISION_ENDPOINT'] ?? null;
        $apiKey = $_ENV['AZURE_VISION_API_KEY'] ?? null;
        
        if (!$endpoint || !$apiKey) {
            throw new Exception('Azure Vision API not configured');
        }
        
        $imageData = file_get_contents($imagePath);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint . '/vision/v3.2/read/analyze');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Ocp-Apim-Subscription-Key: ' . $apiKey,
            'Content-Type: application/octet-stream'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 202) {
            throw new Exception('Azure Vision API request failed');
        }
        
        // Azure returns a URL to check for results
        $operationLocation = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        // This is a simplified implementation - in practice, you'd need to poll for results
        
        throw new Exception('Azure Vision API implementation incomplete');
    }
    
    /**
     * Basic image analysis fallback
     */
    private function basicImageAnalysis($imagePath) {
        // This is a placeholder for basic image analysis
        // In a real implementation, you might use image processing libraries
        // to detect text-like patterns or extract metadata
        
        $imageInfo = getimagesize($imagePath);
        $text = "Image analysis not available. Please use a proper OCR service.";
        
        return [
            'text' => $text,
            'confidence' => 0.1
        ];
    }
    
    /**
     * Validate image file
     */
    private function validateImage($imageData, $filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check file extension
        if (!in_array($extension, $this->supportedFormats)) {
            return [
                'valid' => false,
                'error' => 'Unsupported image format. Supported formats: ' . implode(', ', $this->supportedFormats)
            ];
        }
        
        // Check file size
        if (strlen($imageData) > $this->maxFileSize) {
            return [
                'valid' => false,
                'error' => 'File too large. Maximum size: ' . ($this->maxFileSize / 1024 / 1024) . 'MB'
            ];
        }
        
        // Validate image data
        $tempFile = tempnam(sys_get_temp_dir(), 'img_');
        file_put_contents($tempFile, $imageData);
        
        $imageInfo = getimagesize($tempFile);
        unlink($tempFile);
        
        if ($imageInfo === false) {
            return [
                'valid' => false,
                'error' => 'Invalid image file'
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Get user's OCR results
     */
    public function getUserOCRResults($userId, $limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, filename, original_filename, extracted_text, confidence, created_at
                FROM ocr_results 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get OCR results failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Convert OCR result to text note
     */
    public function convertToTextNote($ocrId, $userId, $title = null) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ocr_results 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$ocrId, $userId]);
            $ocrResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ocrResult) {
                return ['success' => false, 'error' => 'OCR result not found'];
            }
            
            if (empty($ocrResult['extracted_text'])) {
                return ['success' => false, 'error' => 'No text extracted'];
            }
            
            // Create text note
            $noteTitle = $title ?: 'OCR Note - ' . date('Y-m-d H:i:s');
            $noteContent = $ocrResult['extracted_text'];
            
            $stmt = $this->db->prepare("
                INSERT INTO notes (user_id, title, content, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([$userId, $noteTitle, $noteContent]);
            $noteId = $this->db->lastInsertId();
            
            // Link OCR result to text note
            $stmt = $this->db->prepare("
                UPDATE ocr_results 
                SET linked_note_id = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$noteId, $ocrId]);
            
            return [
                'success' => true,
                'note_id' => $noteId,
                'title' => $noteTitle,
                'content' => $noteContent
            ];
            
        } catch (Exception $e) {
            error_log("Convert OCR to text note failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to convert OCR result'];
        }
    }
    
    /**
     * Delete OCR result
     */
    public function deleteOCRResult($ocrId, $userId) {
        try {
            // Get file path
            $stmt = $this->db->prepare("
                SELECT file_path FROM ocr_results 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$ocrId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && file_exists($result['file_path'])) {
                unlink($result['file_path']);
            }
            
            // Delete from database
            $stmt = $this->db->prepare("
                DELETE FROM ocr_results 
                WHERE id = ? AND user_id = ?
            ");
            
            return $stmt->execute([$ocrId, $userId]);
        } catch (Exception $e) {
            error_log("Delete OCR result failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate OCR interface HTML
     */
    public function generateOCRInterface() {
        return '
        <div class="ocr-interface">
            <div class="ocr-upload">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h3>Upload Image for OCR</h3>
                        <p>Drag and drop an image here or click to browse</p>
                        <input type="file" id="imageInput" accept="image/*" style="display: none;">
                        <button class="upload-btn" onclick="document.getElementById(\'imageInput\').click()">
                            Choose File
                        </button>
                    </div>
                </div>
                
                <div class="ocr-options">
                    <label>
                        <input type="checkbox" id="autoConvert" checked>
                        Auto-convert to text note
                    </label>
                    <label>
                        <input type="checkbox" id="enhanceImage" checked>
                        Enhance image before OCR
                    </label>
                </div>
            </div>
            
            <div class="ocr-results">
                <h3>Recent OCR Results</h3>
                <div id="ocrResultsList" class="results-list">
                    <!-- OCR results will be loaded here -->
                </div>
            </div>
        </div>
        
        <style>
        .ocr-interface {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .ocr-upload {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }
        
        .upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover,
        .upload-area.dragover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        
        .upload-content i {
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 16px;
        }
        
        .upload-content h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1f2937;
        }
        
        .upload-content p {
            color: #6b7280;
            margin-bottom: 16px;
        }
        
        .upload-btn {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .upload-btn:hover {
            background: #2563eb;
        }
        
        .ocr-options {
            display: flex;
            gap: 24px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .ocr-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
        }
        
        .ocr-results h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1f2937;
        }
        
        .results-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .ocr-result-item {
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .ocr-result-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .ocr-result-title {
            font-weight: 600;
            color: #1f2937;
        }
        
        .ocr-result-meta {
            font-size: 12px;
            color: #6b7280;
        }
        
        .ocr-result-text {
            background: #f9fafb;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 12px;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .ocr-result-actions {
            display: flex;
            gap: 8px;
        }
        
        .ocr-result-actions button {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            font-size: 12px;
        }
        
        .ocr-result-actions button:hover {
            background: #f9fafb;
        }
        
        .confidence-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .confidence-high {
            background: #dcfce7;
            color: #166534;
        }
        
        .confidence-medium {
            background: #fef3c7;
            color: #92400e;
        }
        
        .confidence-low {
            background: #fee2e2;
            color: #991b1b;
        }
        </style>
        
        <script>
        class OCRInterface {
            constructor() {
                this.initializeElements();
                this.setupEventListeners();
                this.loadOCRResults();
            }
            
            initializeElements() {
                this.uploadArea = document.getElementById("uploadArea");
                this.imageInput = document.getElementById("imageInput");
                this.autoConvert = document.getElementById("autoConvert");
                this.enhanceImage = document.getElementById("enhanceImage");
            }
            
            setupEventListeners() {
                this.uploadArea.addEventListener("click", () => {
                    this.imageInput.click();
                });
                
                this.uploadArea.addEventListener("dragover", (e) => {
                    e.preventDefault();
                    this.uploadArea.classList.add("dragover");
                });
                
                this.uploadArea.addEventListener("dragleave", () => {
                    this.uploadArea.classList.remove("dragover");
                });
                
                this.uploadArea.addEventListener("drop", (e) => {
                    e.preventDefault();
                    this.uploadArea.classList.remove("dragover");
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.processImage(files[0]);
                    }
                });
                
                this.imageInput.addEventListener("change", (e) => {
                    if (e.target.files.length > 0) {
                        this.processImage(e.target.files[0]);
                    }
                });
            }
            
            processImage(file) {
                if (!file.type.startsWith("image/")) {
                    alert("Please select an image file");
                    return;
                }
                
                const formData = new FormData();
                formData.append("image", file);
                formData.append("auto_convert", this.autoConvert.checked);
                formData.append("enhance_image", this.enhanceImage.checked);
                formData.append("csrf_token", document.querySelector("input[name=\'csrf_token\']").value);
                
                // Show loading state
                this.uploadArea.innerHTML = `
                    <div class="upload-content">
                        <i class="fas fa-spinner fa-spin"></i>
                        <h3>Processing Image...</h3>
                        <p>Please wait while we extract text from your image</p>
                    </div>
                `;
                
                fetch("/ocr/process", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.loadOCRResults();
                        alert("OCR completed successfully!");
                        
                        if (data.note_id) {
                            window.location.href = `/notes/${data.note_id}`;
                        }
                    } else {
                        alert("OCR failed: " + data.error);
                    }
                    
                    this.resetUploadArea();
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error processing image");
                    this.resetUploadArea();
                });
            }
            
            resetUploadArea() {
                this.uploadArea.innerHTML = `
                    <div class="upload-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h3>Upload Image for OCR</h3>
                        <p>Drag and drop an image here or click to browse</p>
                        <button class="upload-btn" onclick="document.getElementById(\'imageInput\').click()">
                            Choose File
                        </button>
                    </div>
                `;
            }
            
            loadOCRResults() {
                fetch("/ocr/results")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.displayOCRResults(data.results);
                    }
                })
                .catch(error => {
                    console.error("Error loading OCR results:", error);
                });
            }
            
            displayOCRResults(results) {
                const container = document.getElementById("ocrResultsList");
                container.innerHTML = "";
                
                results.forEach(result => {
                    const resultElement = document.createElement("div");
                    resultElement.className = "ocr-result-item";
                    
                    const confidenceClass = result.confidence > 0.8 ? "confidence-high" : 
                                          result.confidence > 0.5 ? "confidence-medium" : "confidence-low";
                    
                    resultElement.innerHTML = `
                        <div class="ocr-result-header">
                            <div class="ocr-result-title">${result.original_filename}</div>
                            <div class="ocr-result-meta">
                                <span class="confidence-badge ${confidenceClass}">
                                    ${Math.round(result.confidence * 100)}% confidence
                                </span>
                                • ${new Date(result.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div class="ocr-result-text">${result.extracted_text}</div>
                        <div class="ocr-result-actions">
                            <button onclick="convertOCRResult(${result.id})">Convert to Note</button>
                            <button onclick="copyOCRText(${result.id})">Copy Text</button>
                            <button onclick="deleteOCRResult(${result.id})">Delete</button>
                        </div>
                    `;
                    container.appendChild(resultElement);
                });
            }
        }
        
        // Initialize OCR interface when DOM is ready
        document.addEventListener("DOMContentLoaded", () => {
            window.ocrInterface = new OCRInterface();
        });
        
        // Global functions for OCR actions
        function convertOCRResult(resultId) {
            const title = prompt("Enter note title:");
            if (title) {
                fetch(`/ocr/convert/${resultId}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({
                        title: title,
                        csrf_token: document.querySelector("input[name=\'csrf_token\']").value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("OCR result converted to text note!");
                        window.location.href = `/notes/${data.note_id}`;
                    } else {
                        alert("Conversion failed: " + data.error);
                    }
                });
            }
        }
        
        function copyOCRText(resultId) {
            // Find the text in the result element
            const resultElement = document.querySelector(`[onclick="copyOCRText(${resultId})"]`).closest(".ocr-result-item");
            const textElement = resultElement.querySelector(".ocr-result-text");
            const text = textElement.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                alert("Text copied to clipboard!");
            });
        }
        
        function deleteOCRResult(resultId) {
            if (confirm("Are you sure you want to delete this OCR result?")) {
                fetch(`/ocr/delete/${resultId}`, {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({
                        csrf_token: document.querySelector("input[name=\'csrf_token\']").value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.ocrInterface.loadOCRResults();
                    } else {
                        alert("Delete failed: " + data.error);
                    }
                });
            }
        }
        </script>
        ';
    }
}
