import React, { useState, useEffect } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Button,
  IconButton,
  Chip,
  LinearProgress,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  Divider,
} from '@mui/material';
import {
  Add as AddIcon,
  Note as NoteIcon,
  Assignment as TaskIcon,
  Mic as MicIcon,
  CameraAlt as CameraIcon,
  TrendingUp as TrendingUpIcon,
  Schedule as ScheduleIcon,
  CheckCircle as CheckCircleIcon,
} from '@mui/icons-material';
import { useAuth } from '../hooks/useAuth';
import { useElectronAPI } from '../hooks/useElectronAPI';

const Dashboard = () => {
  const [stats, setStats] = useState({
    totalNotes: 0,
    totalTasks: 0,
    completedTasks: 0,
    pendingTasks: 0,
    recentNotes: [],
    upcomingTasks: [],
  });
  const [loading, setLoading] = useState(true);
  
  const { makeAuthenticatedRequest } = useAuth();
  const electronAPI = useElectronAPI();

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      setLoading(true);
      
      // Load notes
      const notesResponse = await makeAuthenticatedRequest('/api/notes?limit=5');
      const notes = notesResponse.notes || [];
      
      // Load tasks
      const tasksResponse = await makeAuthenticatedRequest('/api/tasks?limit=10');
      const tasks = tasksResponse.tasks || [];
      
      // Calculate stats
      const completedTasks = tasks.filter(task => task.status === 'completed').length;
      const pendingTasks = tasks.filter(task => task.status === 'pending').length;
      
      // Get upcoming tasks (due within 7 days)
      const upcomingTasks = tasks
        .filter(task => task.due_date && new Date(task.due_date) <= new Date(Date.now() + 7 * 24 * 60 * 60 * 1000))
        .sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
        .slice(0, 5);
      
      setStats({
        totalNotes: notes.length,
        totalTasks: tasks.length,
        completedTasks,
        pendingTasks,
        recentNotes: notes.slice(0, 5),
        upcomingTasks,
      });
    } catch (error) {
      console.error('Error loading dashboard data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleQuickAction = (action) => {
    switch (action) {
      case 'new-note':
        electronAPI?.onMenuNewNote();
        break;
      case 'new-task':
        electronAPI?.onMenuNewTask();
        break;
      case 'voice-note':
        // Navigate to voice notes
        break;
      case 'ocr':
        // Navigate to OCR
        break;
      default:
        break;
    }
  };

  const getCompletionPercentage = () => {
    if (stats.totalTasks === 0) return 0;
    return Math.round((stats.completedTasks / stats.totalTasks) * 100);
  };

  if (loading) {
    return (
      <Box sx={{ p: 3 }}>
        <LinearProgress />
        <Typography variant="h6" sx={{ mt: 2 }}>
          Loading dashboard...
        </Typography>
      </Box>
    );
  }

  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h4" gutterBottom>
        Dashboard
      </Typography>
      
      {/* Quick Actions */}
      <Card sx={{ mb: 3 }}>
        <CardContent>
          <Typography variant="h6" gutterBottom>
            Quick Actions
          </Typography>
          <Grid container spacing={2}>
            <Grid item xs={12} sm={6} md={3}>
              <Button
                fullWidth
                variant="contained"
                startIcon={<NoteIcon />}
                onClick={() => handleQuickAction('new-note')}
                sx={{ height: 60 }}
              >
                New Note
              </Button>
            </Grid>
            <Grid item xs={12} sm={6} md={3}>
              <Button
                fullWidth
                variant="contained"
                startIcon={<TaskIcon />}
                onClick={() => handleQuickAction('new-task')}
                sx={{ height: 60 }}
              >
                New Task
              </Button>
            </Grid>
            <Grid item xs={12} sm={6} md={3}>
              <Button
                fullWidth
                variant="outlined"
                startIcon={<MicIcon />}
                onClick={() => handleQuickAction('voice-note')}
                sx={{ height: 60 }}
              >
                Voice Note
              </Button>
            </Grid>
            <Grid item xs={12} sm={6} md={3}>
              <Button
                fullWidth
                variant="outlined"
                startIcon={<CameraIcon />}
                onClick={() => handleQuickAction('ocr')}
                sx={{ height: 60 }}
              >
                OCR
              </Button>
            </Grid>
          </Grid>
        </CardContent>
      </Card>

      {/* Statistics */}
      <Grid container spacing={3} sx={{ mb: 3 }}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <NoteIcon color="primary" sx={{ mr: 1 }} />
                <Typography variant="h6">Total Notes</Typography>
              </Box>
              <Typography variant="h4" color="primary">
                {stats.totalNotes}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <TaskIcon color="secondary" sx={{ mr: 1 }} />
                <Typography variant="h6">Total Tasks</Typography>
              </Box>
              <Typography variant="h4" color="secondary">
                {stats.totalTasks}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <CheckCircleIcon color="success" sx={{ mr: 1 }} />
                <Typography variant="h6">Completed</Typography>
              </Box>
              <Typography variant="h4" color="success.main">
                {stats.completedTasks}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <ScheduleIcon color="warning" sx={{ mr: 1 }} />
                <Typography variant="h6">Pending</Typography>
              </Box>
              <Typography variant="h4" color="warning.main">
                {stats.pendingTasks}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Progress and Recent Activity */}
      <Grid container spacing={3}>
        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Task Completion Progress
              </Typography>
              <Box sx={{ mb: 2 }}>
                <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                  <Typography variant="body2">
                    {stats.completedTasks} of {stats.totalTasks} tasks completed
                  </Typography>
                  <Typography variant="body2">
                    {getCompletionPercentage()}%
                  </Typography>
                </Box>
                <LinearProgress 
                  variant="determinate" 
                  value={getCompletionPercentage()} 
                  sx={{ height: 8, borderRadius: 4 }}
                />
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Upcoming Tasks
              </Typography>
              {stats.upcomingTasks.length > 0 ? (
                <List dense>
                  {stats.upcomingTasks.map((task, index) => (
                    <React.Fragment key={task.id}>
                      <ListItem>
                        <ListItemIcon>
                          <TaskIcon color="primary" />
                        </ListItemIcon>
                        <ListItemText
                          primary={task.title}
                          secondary={`Due: ${new Date(task.due_date).toLocaleDateString()}`}
                        />
                        <Chip 
                          label={task.priority} 
                          size="small" 
                          color={task.priority === 'high' ? 'error' : task.priority === 'medium' ? 'warning' : 'default'}
                        />
                      </ListItem>
                      {index < stats.upcomingTasks.length - 1 && <Divider />}
                    </React.Fragment>
                  ))}
                </List>
              ) : (
                <Typography variant="body2" color="text.secondary">
                  No upcoming tasks
                </Typography>
              )}
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Recent Notes */}
      <Card sx={{ mt: 3 }}>
        <CardContent>
          <Typography variant="h6" gutterBottom>
            Recent Notes
          </Typography>
          {stats.recentNotes.length > 0 ? (
            <List>
              {stats.recentNotes.map((note, index) => (
                <React.Fragment key={note.id}>
                  <ListItem>
                    <ListItemIcon>
                      <NoteIcon color="primary" />
                    </ListItemIcon>
                    <ListItemText
                      primary={note.title}
                      secondary={`Updated: ${new Date(note.updated_at).toLocaleDateString()}`}
                    />
                    {note.tags && note.tags.length > 0 && (
                      <Box sx={{ display: 'flex', gap: 1 }}>
                        {note.tags.slice(0, 2).map((tag) => (
                          <Chip key={tag.id} label={tag.name} size="small" />
                        ))}
                      </Box>
                    )}
                  </ListItem>
                  {index < stats.recentNotes.length - 1 && <Divider />}
                </React.Fragment>
              ))}
            </List>
          ) : (
            <Typography variant="body2" color="text.secondary">
              No recent notes
            </Typography>
          )}
        </CardContent>
      </Card>
    </Box>
  );
};

export default Dashboard;
