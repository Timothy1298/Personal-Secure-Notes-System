<?php
namespace Core;

use PDO;
use Exception;

class VoiceNotes {
    private $db;
    private $supportedFormats = ['wav', 'mp3', 'ogg', 'webm'];
    private $maxFileSize = 50 * 1024 * 1024; // 50MB
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Save voice note
     */
    public function saveVoiceNote($userId, $audioData, $filename, $duration = null) {
        try {
            // Validate file
            $validation = $this->validateAudioFile($audioData, $filename);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }
            
            // Generate unique filename
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $uniqueFilename = uniqid() . '_' . time() . '.' . $extension;
            $uploadPath = __DIR__ . '/../../uploads/voice_notes/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Save audio file
            $filePath = $uploadPath . $uniqueFilename;
            if (!file_put_contents($filePath, $audioData)) {
                return ['success' => false, 'error' => 'Failed to save audio file'];
            }
            
            // Save to database
            $stmt = $this->db->prepare("
                INSERT INTO voice_notes (user_id, filename, original_filename, file_path, duration, file_size, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $uniqueFilename,
                $filename,
                $filePath,
                $duration,
                strlen($audioData)
            ]);
            
            $voiceNoteId = $this->db->lastInsertId();
            
            return [
                'success' => true,
                'voice_note_id' => $voiceNoteId,
                'filename' => $uniqueFilename,
                'duration' => $duration
            ];
            
        } catch (Exception $e) {
            error_log("Voice note save failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to save voice note'];
        }
    }
    
    /**
     * Get user's voice notes
     */
    public function getUserVoiceNotes($userId, $limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, filename, original_filename, duration, file_size, created_at,
                       transcription, is_processed
                FROM voice_notes 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get voice notes failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get voice note by ID
     */
    public function getVoiceNote($voiceNoteId, $userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM voice_notes 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$voiceNoteId, $userId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get voice note failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update transcription
     */
    public function updateTranscription($voiceNoteId, $userId, $transcription) {
        try {
            $stmt = $this->db->prepare("
                UPDATE voice_notes 
                SET transcription = ?, is_processed = 1, updated_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            
            return $stmt->execute([$transcription, $voiceNoteId, $userId]);
        } catch (Exception $e) {
            error_log("Update transcription failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete voice note
     */
    public function deleteVoiceNote($voiceNoteId, $userId) {
        try {
            // Get file path
            $voiceNote = $this->getVoiceNote($voiceNoteId, $userId);
            if (!$voiceNote) {
                return false;
            }
            
            // Delete file
            if (file_exists($voiceNote['file_path'])) {
                unlink($voiceNote['file_path']);
            }
            
            // Delete from database
            $stmt = $this->db->prepare("
                DELETE FROM voice_notes 
                WHERE id = ? AND user_id = ?
            ");
            
            return $stmt->execute([$voiceNoteId, $userId]);
        } catch (Exception $e) {
            error_log("Delete voice note failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert voice note to text note
     */
    public function convertToTextNote($voiceNoteId, $userId, $title = null) {
        try {
            $voiceNote = $this->getVoiceNote($voiceNoteId, $userId);
            if (!$voiceNote) {
                return ['success' => false, 'error' => 'Voice note not found'];
            }
            
            if (empty($voiceNote['transcription'])) {
                return ['success' => false, 'error' => 'No transcription available'];
            }
            
            // Create text note
            $noteTitle = $title ?: 'Voice Note - ' . date('Y-m-d H:i:s');
            $noteContent = $voiceNote['transcription'];
            
            $stmt = $this->db->prepare("
                INSERT INTO notes (user_id, title, content, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([$userId, $noteTitle, $noteContent]);
            $noteId = $this->db->lastInsertId();
            
            // Link voice note to text note
            $stmt = $this->db->prepare("
                UPDATE voice_notes 
                SET linked_note_id = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$noteId, $voiceNoteId]);
            
            return [
                'success' => true,
                'note_id' => $noteId,
                'title' => $noteTitle,
                'content' => $noteContent
            ];
            
        } catch (Exception $e) {
            error_log("Convert to text note failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to convert voice note'];
        }
    }
    
    /**
     * Validate audio file
     */
    private function validateAudioFile($audioData, $filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check file extension
        if (!in_array($extension, $this->supportedFormats)) {
            return [
                'valid' => false,
                'error' => 'Unsupported audio format. Supported formats: ' . implode(', ', $this->supportedFormats)
            ];
        }
        
        // Check file size
        if (strlen($audioData) > $this->maxFileSize) {
            return [
                'valid' => false,
                'error' => 'File too large. Maximum size: ' . ($this->maxFileSize / 1024 / 1024) . 'MB'
            ];
        }
        
        // Basic audio file validation
        if (strlen($audioData) < 100) {
            return [
                'valid' => false,
                'error' => 'File appears to be corrupted or too small'
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Get voice note statistics
     */
    public function getVoiceNoteStats($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_notes,
                    SUM(duration) as total_duration,
                    SUM(file_size) as total_size,
                    COUNT(CASE WHEN is_processed = 1 THEN 1 END) as processed_notes,
                    COUNT(CASE WHEN linked_note_id IS NOT NULL THEN 1 END) as converted_notes
                FROM voice_notes 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get voice note stats failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate voice note HTML interface
     */
    public function generateVoiceNoteInterface() {
        return '
        <div class="voice-notes-interface">
            <div class="voice-recorder">
                <div class="recorder-controls">
                    <button id="startRecording" class="record-btn start">
                        <i class="fas fa-microphone"></i>
                        <span>Start Recording</span>
                    </button>
                    <button id="stopRecording" class="record-btn stop" disabled>
                        <i class="fas fa-stop"></i>
                        <span>Stop Recording</span>
                    </button>
                    <button id="pauseRecording" class="record-btn pause" disabled>
                        <i class="fas fa-pause"></i>
                        <span>Pause</span>
                    </button>
                </div>
                
                <div class="recording-status">
                    <div class="recording-indicator">
                        <div class="pulse-dot"></div>
                        <span id="recordingTime">00:00</span>
                    </div>
                    <div class="audio-visualizer">
                        <canvas id="audioVisualizer" width="300" height="50"></canvas>
                    </div>
                </div>
                
                <div class="recording-options">
                    <label>
                        <input type="checkbox" id="autoTranscribe" checked>
                        Auto-transcribe after recording
                    </label>
                    <label>
                        <input type="checkbox" id="convertToNote" checked>
                        Convert to text note
                    </label>
                </div>
            </div>
            
            <div class="voice-notes-list">
                <h3>Recent Voice Notes</h3>
                <div id="voiceNotesList" class="notes-list">
                    <!-- Voice notes will be loaded here -->
                </div>
            </div>
        </div>
        
        <style>
        .voice-notes-interface {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .voice-recorder {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }
        
        .recorder-controls {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .record-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .record-btn.start {
            background: #10b981;
            color: white;
        }
        
        .record-btn.stop {
            background: #ef4444;
            color: white;
        }
        
        .record-btn.pause {
            background: #f59e0b;
            color: white;
        }
        
        .record-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .record-btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .recording-status {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .recording-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .pulse-dot {
            width: 12px;
            height: 12px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .audio-visualizer {
            display: flex;
            justify-content: center;
        }
        
        #audioVisualizer {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        
        .recording-options {
            display: flex;
            gap: 24px;
            justify-content: center;
        }
        
        .recording-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
        }
        
        .voice-notes-list h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1f2937;
        }
        
        .notes-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .voice-note-item {
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .voice-note-play {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .voice-note-info {
            flex: 1;
        }
        
        .voice-note-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .voice-note-meta {
            font-size: 12px;
            color: #6b7280;
        }
        
        .voice-note-actions {
            display: flex;
            gap: 8px;
        }
        
        .voice-note-actions button {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            font-size: 12px;
        }
        
        .voice-note-actions button:hover {
            background: #f9fafb;
        }
        </style>
        
        <script>
        class VoiceRecorder {
            constructor() {
                this.mediaRecorder = null;
                this.audioChunks = [];
                this.isRecording = false;
                this.isPaused = false;
                this.recordingStartTime = null;
                this.recordingTimer = null;
                
                this.initializeElements();
                this.setupEventListeners();
            }
            
            initializeElements() {
                this.startBtn = document.getElementById("startRecording");
                this.stopBtn = document.getElementById("stopRecording");
                this.pauseBtn = document.getElementById("pauseRecording");
                this.timeDisplay = document.getElementById("recordingTime");
                this.visualizer = document.getElementById("audioVisualizer");
                this.autoTranscribe = document.getElementById("autoTranscribe");
                this.convertToNote = document.getElementById("convertToNote");
            }
            
            setupEventListeners() {
                this.startBtn.addEventListener("click", () => this.startRecording());
                this.stopBtn.addEventListener("click", () => this.stopRecording());
                this.pauseBtn.addEventListener("click", () => this.togglePause());
            }
            
            async startRecording() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    
                    this.mediaRecorder = new MediaRecorder(stream);
                    this.audioChunks = [];
                    
                    this.mediaRecorder.ondataavailable = (event) => {
                        this.audioChunks.push(event.data);
                    };
                    
                    this.mediaRecorder.onstop = () => {
                        this.processRecording();
                    };
                    
                    this.mediaRecorder.start();
                    this.isRecording = true;
                    this.recordingStartTime = Date.now();
                    
                    this.updateUI();
                    this.startTimer();
                    this.startVisualizer(stream);
                    
                } catch (error) {
                    console.error("Error starting recording:", error);
                    alert("Could not start recording. Please check microphone permissions.");
                }
            }
            
            stopRecording() {
                if (this.mediaRecorder && this.isRecording) {
                    this.mediaRecorder.stop();
                    this.isRecording = false;
                    this.isPaused = false;
                    
                    this.updateUI();
                    this.stopTimer();
                    this.stopVisualizer();
                    
                    // Stop all tracks
                    this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
                }
            }
            
            togglePause() {
                if (this.isPaused) {
                    this.mediaRecorder.resume();
                    this.isPaused = false;
                    this.startTimer();
                } else {
                    this.mediaRecorder.pause();
                    this.isPaused = true;
                    this.stopTimer();
                }
                this.updateUI();
            }
            
            processRecording() {
                const audioBlob = new Blob(this.audioChunks, { type: "audio/wav" });
                const formData = new FormData();
                formData.append("audio", audioBlob, "recording.wav");
                formData.append("duration", Math.floor((Date.now() - this.recordingStartTime) / 1000));
                formData.append("auto_transcribe", this.autoTranscribe.checked);
                formData.append("convert_to_note", this.convertToNote.checked);
                formData.append("csrf_token", document.querySelector("input[name=\'csrf_token\']").value);
                
                fetch("/voice-notes/save", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.loadVoiceNotes();
                        alert("Voice note saved successfully!");
                    } else {
                        alert("Error saving voice note: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error saving voice note");
                });
            }
            
            updateUI() {
                this.startBtn.disabled = this.isRecording;
                this.stopBtn.disabled = !this.isRecording;
                this.pauseBtn.disabled = !this.isRecording;
                
                if (this.isPaused) {
                    this.pauseBtn.innerHTML = \'<i class="fas fa-play"></i><span>Resume</span>\';
                } else {
                    this.pauseBtn.innerHTML = \'<i class="fas fa-pause"></i><span>Pause</span>\';
                }
            }
            
            startTimer() {
                this.recordingTimer = setInterval(() => {
                    const elapsed = Math.floor((Date.now() - this.recordingStartTime) / 1000);
                    const minutes = Math.floor(elapsed / 60);
                    const seconds = elapsed % 60;
                    this.timeDisplay.textContent = `${minutes.toString().padStart(2, \'0\')}:${seconds.toString().padStart(2, \'0\')}`;
                }, 1000);
            }
            
            stopTimer() {
                if (this.recordingTimer) {
                    clearInterval(this.recordingTimer);
                    this.recordingTimer = null;
                }
            }
            
            startVisualizer(stream) {
                const audioContext = new AudioContext();
                const analyser = audioContext.createAnalyser();
                const source = audioContext.createMediaStreamSource(stream);
                
                source.connect(analyser);
                analyser.fftSize = 256;
                
                const bufferLength = analyser.frequencyBinCount;
                const dataArray = new Uint8Array(bufferLength);
                
                const canvas = this.visualizer;
                const ctx = canvas.getContext("2d");
                
                const draw = () => {
                    if (!this.isRecording) return;
                    
                    requestAnimationFrame(draw);
                    
                    analyser.getByteFrequencyData(dataArray);
                    
                    ctx.fillStyle = "#f3f4f6";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    
                    const barWidth = (canvas.width / bufferLength) * 2.5;
                    let barHeight;
                    let x = 0;
                    
                    for (let i = 0; i < bufferLength; i++) {
                        barHeight = (dataArray[i] / 255) * canvas.height;
                        
                        ctx.fillStyle = `rgb(${barHeight + 100}, 50, 50)`;
                        ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
                        
                        x += barWidth + 1;
                    }
                };
                
                draw();
            }
            
            stopVisualizer() {
                const canvas = this.visualizer;
                const ctx = canvas.getContext("2d");
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            
            loadVoiceNotes() {
                fetch("/voice-notes/list")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.displayVoiceNotes(data.voice_notes);
                    }
                })
                .catch(error => {
                    console.error("Error loading voice notes:", error);
                });
            }
            
            displayVoiceNotes(notes) {
                const container = document.getElementById("voiceNotesList");
                container.innerHTML = "";
                
                notes.forEach(note => {
                    const noteElement = document.createElement("div");
                    noteElement.className = "voice-note-item";
                    noteElement.innerHTML = `
                        <button class="voice-note-play" onclick="playVoiceNote(${note.id})">
                            <i class="fas fa-play"></i>
                        </button>
                        <div class="voice-note-info">
                            <div class="voice-note-title">${note.original_filename}</div>
                            <div class="voice-note-meta">
                                ${note.duration ? Math.floor(note.duration / 60) + ":" + (note.duration % 60).toString().padStart(2, \'0\') : "Unknown"} • 
                                ${new Date(note.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div class="voice-note-actions">
                            <button onclick="transcribeVoiceNote(${note.id})">Transcribe</button>
                            <button onclick="convertVoiceNote(${note.id})">Convert to Note</button>
                            <button onclick="deleteVoiceNote(${note.id})">Delete</button>
                        </div>
                    `;
                    container.appendChild(noteElement);
                });
            }
        }
        
        // Initialize voice recorder when DOM is ready
        document.addEventListener("DOMContentLoaded", () => {
            window.voiceRecorder = new VoiceRecorder();
            window.voiceRecorder.loadVoiceNotes();
        });
        
        // Global functions for voice note actions
        function playVoiceNote(noteId) {
            // Implementation for playing voice note
            console.log("Playing voice note:", noteId);
        }
        
        function transcribeVoiceNote(noteId) {
            fetch(`/voice-notes/transcribe/${noteId}`, {
                method: "POST",
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
                    alert("Transcription completed!");
                    window.voiceRecorder.loadVoiceNotes();
                } else {
                    alert("Transcription failed: " + data.error);
                }
            });
        }
        
        function convertVoiceNote(noteId) {
            const title = prompt("Enter note title:");
            if (title) {
                fetch(`/voice-notes/convert/${noteId}`, {
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
                        alert("Voice note converted to text note!");
                        window.location.href = `/notes/${data.note_id}`;
                    } else {
                        alert("Conversion failed: " + data.error);
                    }
                });
            }
        }
        
        function deleteVoiceNote(noteId) {
            if (confirm("Are you sure you want to delete this voice note?")) {
                fetch(`/voice-notes/delete/${noteId}`, {
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
                        window.voiceRecorder.loadVoiceNotes();
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
