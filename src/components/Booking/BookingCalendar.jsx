import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import './BookingCalendar.css';

const BookingCalendar = () => {
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [view, setView] = useState('month'); // month, week, day

  const appointments = [
    { id: 1, time: '09:00', patient: 'John Doe', doctor: 'Dr. Smith', status: 'confirmed' },
    { id: 2, time: '10:30', patient: 'Jane Wilson', doctor: 'Dr. Johnson', status: 'pending' },
    { id: 3, time: '14:00', patient: 'Mike Brown', doctor: 'Dr. Smith', status: 'confirmed' },
    { id: 4, time: '15:30', patient: 'Sarah Davis', doctor: 'Dr. Wilson', status: 'cancelled' },
  ];

  const getStatusColor = (status) => {
    switch (status) {
      case 'confirmed': return '#2ecc71';
      case 'pending': return '#f39c12';
      case 'cancelled': return '#e74c3c';
      default: return '#95a5a6';
    }
  };

  return (
    <div className="medx360-booking-calendar">
      <div className="medx360-calendar-header">
        <h2>Booking Calendar</h2>
        <div className="medx360-calendar-controls">
          <div className="medx360-view-toggle">
            <button 
              className={view === 'month' ? 'active' : ''} 
              onClick={() => setView('month')}
            >
              Month
            </button>
            <button 
              className={view === 'week' ? 'active' : ''} 
              onClick={() => setView('week')}
            >
              Week
            </button>
            <button 
              className={view === 'day' ? 'active' : ''} 
              onClick={() => setView('day')}
            >
              Day
            </button>
          </div>
          <Link to="/booking/new" className="medx360-new-booking-btn">
            ➕ New Booking
          </Link>
        </div>
      </div>

      <div className="medx360-calendar-content">
        {view === 'month' && (
          <div className="medx360-month-view">
            <div className="medx360-calendar-grid">
              {/* Calendar grid would go here */}
              <div className="medx360-calendar-placeholder">
                <h3>Calendar View</h3>
                <p>Full calendar implementation would go here</p>
                <p>Showing appointments for {selectedDate.toLocaleDateString()}</p>
              </div>
            </div>
          </div>
        )}

        {view === 'day' && (
          <div className="medx360-day-view">
            <h3>Today's Appointments</h3>
            <div className="medx360-appointments-list">
              {appointments.map(appointment => (
                <div key={appointment.id} className="medx360-appointment-card">
                  <div className="medx360-appointment-time">
                    {appointment.time}
                  </div>
                  <div className="medx360-appointment-details">
                    <h4>{appointment.patient}</h4>
                    <p>Doctor: {appointment.doctor}</p>
                    <span 
                      className="medx360-appointment-status"
                      style={{ color: getStatusColor(appointment.status) }}
                    >
                      {appointment.status.toUpperCase()}
                    </span>
                  </div>
                  <div className="medx360-appointment-actions">
                    <button className="medx360-btn medx360-btn-sm">Edit</button>
                    <button className="medx360-btn medx360-btn-sm medx360-btn-danger">Cancel</button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default BookingCalendar;
