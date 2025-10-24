import React, {useState, useEffect, useRef} from 'react';
import {
  View,
  StyleSheet,
  Alert,
  PermissionsAndroid,
  Platform,
  FlatList,
  TouchableOpacity,
} from 'react-native';
import {
  Card,
  Title,
  Paragraph,
  Button,
  IconButton,
  useTheme,
  ActivityIndicator,
  Chip,
} from 'react-native-paper';
import AudioRecorderPlayer from 'react-native-audio-recorder-player';
import {useAuth} from '../context/AuthContext';
import {useNetwork} from '../context/NetworkContext';

const VoiceNotesScreen = () => {
  const [isRecording, setIsRecording] = useState(false);
  const [isPlaying, setIsPlaying] = useState(false);
  const [recordingTime, setRecordingTime] = useState(0);
  const [voiceNotes, setVoiceNotes] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [currentPlayingId, setCurrentPlayingId] = useState(null);

  const audioRecorderPlayer = useRef(new AudioRecorderPlayer()).current;
  const recordingTimer = useRef(null);

  const {makeAuthenticatedRequest, token} = useAuth();
  const {isConnected} = useNetwork();
  const theme = useTheme();

  useEffect(() => {
    loadVoiceNotes();
    return () => {
      if (recordingTimer.current) {
        clearInterval(recordingTimer.current);
      }
      audioRecorderPlayer.stopPlayer();
    };
  }, []);

  const requestPermissions = async () => {
    if (Platform.OS === 'android') {
      try {
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.RECORD_AUDIO,
          {
            title: 'Microphone Permission',
            message: 'This app needs access to your microphone to record voice notes.',
            buttonNeutral: 'Ask Me Later',
            buttonNegative: 'Cancel',
            buttonPositive: 'OK',
          },
        );
        return granted === PermissionsAndroid.RESULTS.GRANTED;
      } catch (err) {
        console.warn(err);
        return false;
      }
    }
    return true;
  };

  const startRecording = async () => {
    const hasPermission = await requestPermissions();
    if (!hasPermission) {
      Alert.alert('Permission Denied', 'Microphone permission is required to record voice notes.');
      return;
    }

    try {
      const result = await audioRecorderPlayer.startRecorder();
      console.log('Recording started:', result);
      
      setIsRecording(true);
      setRecordingTime(0);
      
      recordingTimer.current = setInterval(() => {
        setRecordingTime(prev => prev + 1);
      }, 1000);
    } catch (error) {
      console.error('Error starting recording:', error);
      Alert.alert('Error', 'Failed to start recording');
    }
  };

  const stopRecording = async () => {
    try {
      const result = await audioRecorderPlayer.stopRecorder();
      console.log('Recording stopped:', result);
      
      setIsRecording(false);
      if (recordingTimer.current) {
        clearInterval(recordingTimer.current);
      }

      if (result) {
        await uploadVoiceNote(result);
      }
    } catch (error) {
      console.error('Error stopping recording:', error);
      Alert.alert('Error', 'Failed to stop recording');
    }
  };

  const uploadVoiceNote = async (filePath) => {
    if (!isConnected) {
      Alert.alert('No Internet', 'Please check your internet connection to upload voice notes.');
      return;
    }

    setIsLoading(true);
    try {
      const formData = new FormData();
      formData.append('audio', {
        uri: filePath,
        type: 'audio/m4a',
        name: `voice_note_${Date.now()}.m4a`,
      });
      formData.append('duration', recordingTime);

      const response = await makeAuthenticatedRequest('/api/voice-notes/upload', {
        method: 'POST',
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        body: formData,
      });

      if (response.success) {
        Alert.alert('Success', 'Voice note saved successfully!');
        loadVoiceNotes();
      } else {
        Alert.alert('Error', response.error || 'Failed to save voice note');
      }
    } catch (error) {
      console.error('Upload error:', error);
      Alert.alert('Error', 'Failed to upload voice note');
    } finally {
      setIsLoading(false);
    }
  };

  const loadVoiceNotes = async () => {
    if (!isConnected) return;

    setIsLoading(true);
    try {
      const response = await makeAuthenticatedRequest('/api/voice-notes');
      if (response.success) {
        setVoiceNotes(response.voice_notes || []);
      }
    } catch (error) {
      console.error('Error loading voice notes:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const playVoiceNote = async (voiceNote) => {
    try {
      if (currentPlayingId === voiceNote.id) {
        await audioRecorderPlayer.stopPlayer();
        setCurrentPlayingId(null);
        setIsPlaying(false);
      } else {
        if (currentPlayingId) {
          await audioRecorderPlayer.stopPlayer();
        }
        
        const result = await audioRecorderPlayer.startPlayer(voiceNote.file_url);
        console.log('Playing:', result);
        
        setCurrentPlayingId(voiceNote.id);
        setIsPlaying(true);
        
        audioRecorderPlayer.addPlayBackListener((e) => {
          if (e.current_position === e.duration) {
            setCurrentPlayingId(null);
            setIsPlaying(false);
          }
        });
      }
    } catch (error) {
      console.error('Error playing voice note:', error);
      Alert.alert('Error', 'Failed to play voice note');
    }
  };

  const deleteVoiceNote = async (voiceNoteId) => {
    Alert.alert(
      'Delete Voice Note',
      'Are you sure you want to delete this voice note?',
      [
        {text: 'Cancel', style: 'cancel'},
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              const response = await makeAuthenticatedRequest(
                `/api/voice-notes/${voiceNoteId}`,
                {method: 'DELETE'}
              );
              
              if (response.success) {
                loadVoiceNotes();
              } else {
                Alert.alert('Error', 'Failed to delete voice note');
              }
            } catch (error) {
              console.error('Delete error:', error);
              Alert.alert('Error', 'Failed to delete voice note');
            }
          },
        },
      ]
    );
  };

  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  const renderVoiceNote = ({item}) => (
    <Card style={[styles.voiceNoteCard, {backgroundColor: theme.colors.surface}]}>
      <Card.Content>
        <View style={styles.voiceNoteHeader}>
          <Title style={[styles.voiceNoteTitle, {color: theme.colors.onSurface}]}>
            {item.original_filename}
          </Title>
          <View style={styles.voiceNoteActions}>
            <IconButton
              icon={currentPlayingId === item.id ? 'stop' : 'play'}
              onPress={() => playVoiceNote(item)}
              iconColor={theme.colors.primary}
            />
            <IconButton
              icon="delete"
              onPress={() => deleteVoiceNote(item.id)}
              iconColor={theme.colors.error}
            />
          </View>
        </View>
        
        <View style={styles.voiceNoteMeta}>
          <Chip icon="clock" style={styles.chip}>
            {formatTime(item.duration || 0)}
          </Chip>
          <Chip icon="calendar" style={styles.chip}>
            {new Date(item.created_at).toLocaleDateString()}
          </Chip>
          {item.is_processed && (
            <Chip icon="check" style={[styles.chip, {backgroundColor: theme.colors.primaryContainer}]}>
              Transcribed
            </Chip>
          )}
        </View>
        
        {item.transcription && (
          <Paragraph style={[styles.transcription, {color: theme.colors.onSurfaceVariant}]}>
            {item.transcription.substring(0, 100)}...
          </Paragraph>
        )}
      </Card.Content>
    </Card>
  );

  return (
    <View style={[styles.container, {backgroundColor: theme.colors.background}]}>
      <View style={styles.recordingSection}>
        <Card style={[styles.recordingCard, {backgroundColor: theme.colors.surface}]}>
          <Card.Content style={styles.recordingContent}>
            <Title style={[styles.recordingTitle, {color: theme.colors.onSurface}]}>
              Voice Recorder
            </Title>
            
            <View style={styles.recordingControls}>
              <Button
                mode={isRecording ? 'contained' : 'outlined'}
                onPress={isRecording ? stopRecording : startRecording}
                icon={isRecording ? 'stop' : 'microphone'}
                style={styles.recordButton}
                buttonColor={isRecording ? theme.colors.error : theme.colors.primary}
                disabled={isLoading}>
                {isRecording ? 'Stop Recording' : 'Start Recording'}
              </Button>
            </View>
            
            {isRecording && (
              <View style={styles.recordingStatus}>
                <ActivityIndicator color={theme.colors.error} />
                <Paragraph style={[styles.recordingTime, {color: theme.colors.onSurface}]}>
                  Recording: {formatTime(recordingTime)}
                </Paragraph>
              </View>
            )}
          </Card.Content>
        </Card>
      </View>

      <View style={styles.notesSection}>
        <Title style={[styles.sectionTitle, {color: theme.colors.onBackground}]}>
          Voice Notes
        </Title>
        
        {isLoading ? (
          <ActivityIndicator style={styles.loader} />
        ) : (
          <FlatList
            data={voiceNotes}
            renderItem={renderVoiceNote}
            keyExtractor={(item) => item.id.toString()}
            contentContainerStyle={styles.notesList}
            showsVerticalScrollIndicator={false}
          />
        )}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 16,
  },
  recordingSection: {
    marginBottom: 24,
  },
  recordingCard: {
    elevation: 4,
  },
  recordingContent: {
    alignItems: 'center',
    padding: 24,
  },
  recordingTitle: {
    marginBottom: 16,
  },
  recordingControls: {
    marginBottom: 16,
  },
  recordButton: {
    minWidth: 200,
  },
  recordingStatus: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  recordingTime: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  notesSection: {
    flex: 1,
  },
  sectionTitle: {
    marginBottom: 16,
  },
  notesList: {
    paddingBottom: 16,
  },
  voiceNoteCard: {
    marginBottom: 12,
    elevation: 2,
  },
  voiceNoteHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  voiceNoteTitle: {
    flex: 1,
    fontSize: 16,
  },
  voiceNoteActions: {
    flexDirection: 'row',
  },
  voiceNoteMeta: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 8,
  },
  chip: {
    marginRight: 4,
  },
  transcription: {
    fontSize: 14,
    fontStyle: 'italic',
  },
  loader: {
    marginTop: 32,
  },
});

export default VoiceNotesScreen;
