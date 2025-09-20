import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import { appointmentAPI } from '../../utils/api';
import { useToast } from '../Shared/ToastContext';
import './BookingList.css';

const AppointmentList = () => {
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('all');
  const [filterDate, setFilterDate] = useState('');
  const [pagination, setPagination] = useState({ page: 1, per_page: 20, total: 0 });
  const { showToast } = useToast();

  // Load appointment data from API
  useEffect(() => {
    loadAppointmentData();
  }, [pagination.page, searchTerm, filterStatus, filterDate]);

  const loadAppointmentData = async () => {
    try {
      setLoading(true);
      
      const params = {
        page: pagination.page,
        per_page: pagination.per_page,
        search: searchTerm || undefined,
        status: filterStatus === 'all' ? undefined : filterStatus,
        date_from: filterDate || undefined,
        date_to: filterDate || undefined
      };

      const response = await appointmentAPI.getAll(params);
      
      setAppointments(response.data || []);
      setPagination(prev => ({
        ...prev,
        total: response.total || 0,
        total_pages: response.total_pages || 1
      }));
      
    } catch (error) {
      console.error('Error loading appointment data:', error);
      showToast('Failed to load appointment data', 'error');
      setAppointments([]);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this appointment?')) {
      try {
        await appointmentAPI.delete(id);
        showToast('Appointment deleted successfully', 'success');
        loadAppointmentData(); // Reload data
      } catch (error) {
        console.error('Error deleting appointment:', error);
        showToast('Failed to delete appointment', 'error');
      }
    }
  };

  const handleStatusUpdate = async (id, newStatus) => {
    try {
      await appointmentAPI.update(id, { status: newStatus });
      showToast(`Appointment ${newStatus} successfully`, 'success');
      loadAppointmentData(); // Reload data
    } catch (error) {
      console.error('Error updating appointment status:', error);
      showToast('Failed to update appointment status', 'error');
    }
  };

  const getStatusBadge = (status) => {
    const statusConfig = {
      scheduled: { text: '📅 Scheduled', class: 'scheduled', color: '#2CA6A4' },
      confirmed: { text: '✅ Confirmed', class: 'confirmed', color: '#56C596' },
      completed: { text: '✅ Completed', class: 'completed', color: '#28A745' },
      cancelled: { text: '❌ Cancelled', class: 'cancelled', color: '#DC3545' },
      no_show: { text: '⏰ No Show', class: 'no-show', color: '#FFC107' }
    };
    return statusConfig[status] || statusConfig.scheduled;
  };

  const formatDateTime = (dateString, timeString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const formattedDate = date.toLocaleDateString();
    const formattedTime = timeString || 'N/A';
    return `${formattedDate} at ${formattedTime}`;
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount || 0);
  };

  if (loading) {
    return (
      <div className="medx360-booking-list">
        <div className="medx360-loading">
          <div className="medx360-spinner"></div>
          <p>Loading appointments...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="medx360-booking-list">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>Appointment Management</h1>
            <p>Manage your appointments and bookings</p>
          </div>
          <div className="medx360-header-actions">
            <a
              href={getWordPressUrl(WORDPRESS_PAGES.BOOKING_NEW)}
              className="medx360-btn medx360-btn-primary"
            >
              <span>➕</span> New Appointment
            </a>
          </div>
        </div>
      </div>

      <div className="medx360-filters">
        <div className="medx360-search-box">
          <input
            type="text"
            placeholder="Search appointments..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="medx360-search-input"
          />
          <span className="medx360-search-icon">🔍</span>
        </div>
        
        <div className="medx360-filter-group">
          <label>Filter by Status:</label>
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="medx360-filter-select"
          >
            <option value="all">All Status</option>
            <option value="scheduled">Scheduled</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
            <option value="no_show">No Show</option>
          </select>
        </div>

        <div className="medx360-filter-group">
          <label>Filter by Date:</label>
          <input
            type="date"
            value={filterDate}
            onChange={(e) => setFilterDate(e.target.value)}
            className="medx360-filter-input"
          />
        </div>
      </div>

      <div className="medx360-stats-cards">
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">📅</div>
          <div className="medx360-stat-content">
            <h3>{pagination.total}</h3>
            <p>Total Appointments</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">✅</div>
          <div className="medx360-stat-content">
            <h3>{appointments.filter(a => a.status === 'completed').length}</h3>
            <p>Completed</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">📅</div>
          <div className="medx360-stat-content">
            <h3>{appointments.filter(a => a.status === 'scheduled').length}</h3>
            <p>Scheduled</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">❌</div>
          <div className="medx360-stat-content">
            <h3>{appointments.filter(a => a.status === 'cancelled').length}</h3>
            <p>Cancelled</p>
          </div>
        </div>
      </div>

      <div className="medx360-appointment-grid">
        {appointments.map(appointment => {
          const statusBadge = getStatusBadge(appointment.status);
          return (
            <div key={appointment.id} className="medx360-appointment-card">
              <div className="medx360-appointment-header">
                <div className="medx360-appointment-time">
                  <span className="medx360-time-icon">🕐</span>
                  <span>{formatDateTime(appointment.appointment_date, appointment.appointment_time)}</span>
                </div>
                <span className={`medx360-status-badge ${statusBadge.class}`} style={{ backgroundColor: statusBadge.color }}>
                  {statusBadge.text}
                </span>
              </div>
              
              <div className="medx360-appointment-content">
                <div className="medx360-appointment-info">
                  <h3>Patient: {appointment.patient_name || 'Unknown'}</h3>
                  <p>Staff: {appointment.staff_name || 'Unknown'}</p>
                  <p>Type: {appointment.appointment_type || 'Consultation'}</p>
                  <p>Duration: {appointment.duration || 30} minutes</p>
                  {appointment.cost && (
                    <p>Cost: {formatCurrency(appointment.cost)}</p>
                  )}
                </div>
                
                {appointment.notes && (
                  <div className="medx360-appointment-notes">
                    <strong>Notes:</strong>
                    <p>{appointment.notes}</p>
                  </div>
                )}
              </div>
              
              <div className="medx360-appointment-actions">
                <a
                  href={getWordPressUrl(`appointments/edit/${appointment.id}`)}
                  className="medx360-btn medx360-btn-secondary medx360-btn-sm"
                >
                  ✏️ Edit
                </a>
                
                {appointment.status === 'scheduled' && (
                  <button
                    onClick={() => handleStatusUpdate(appointment.id, 'confirmed')}
                    className="medx360-btn medx360-btn-success medx360-btn-sm"
                  >
                    ✅ Confirm
                  </button>
                )}
                
                {appointment.status === 'confirmed' && (
                  <button
                    onClick={() => handleStatusUpdate(appointment.id, 'completed')}
                    className="medx360-btn medx360-btn-success medx360-btn-sm"
                  >
                    ✅ Complete
                  </button>
                )}
                
                {appointment.status !== 'cancelled' && appointment.status !== 'completed' && (
                  <button
                    onClick={() => handleStatusUpdate(appointment.id, 'cancelled')}
                    className="medx360-btn medx360-btn-warning medx360-btn-sm"
                  >
                    ❌ Cancel
                  </button>
                )}
                
                <button
                  onClick={() => handleDelete(appointment.id)}
                  className="medx360-btn medx360-btn-danger medx360-btn-sm"
                >
                  🗑️ Delete
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {appointments.length === 0 && !loading && (
        <div className="medx360-empty-state">
          <div className="medx360-empty-icon">📅</div>
          <h3>No appointments found</h3>
          <p>Try adjusting your search criteria or create new appointments.</p>
          <a
            href={getWordPressUrl(WORDPRESS_PAGES.BOOKING_NEW)}
            className="medx360-btn medx360-btn-primary"
          >
            Create First Appointment
          </a>
        </div>
      )}

      {/* Pagination */}
      {pagination.total_pages > 1 && (
        <div className="medx360-pagination">
          <button
            onClick={() => setPagination(prev => ({ ...prev, page: prev.page - 1 }))}
            disabled={pagination.page === 1}
            className="medx360-btn medx360-btn-secondary"
          >
            ← Previous
          </button>
          <span className="medx360-pagination-info">
            Page {pagination.page} of {pagination.total_pages}
          </span>
          <button
            onClick={() => setPagination(prev => ({ ...prev, page: prev.page + 1 }))}
            disabled={pagination.page === pagination.total_pages}
            className="medx360-btn medx360-btn-secondary"
          >
            Next →
          </button>
        </div>
      )}
    </div>
  );
};

export default AppointmentList;
