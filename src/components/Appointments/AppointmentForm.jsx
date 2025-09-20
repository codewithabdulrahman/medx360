import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import './BookingForm.css';

const BookingForm = () => {
  const [formData, setFormData] = useState({
    patient: '',
    doctor: '',
    date: '',
    time: '',
    duration: '30',
    notes: '',
    status: 'confirmed'
  });

  const doctors = [
    { id: 1, name: 'Dr. Smith', specialty: 'General Medicine' },
    { id: 2, name: 'Dr. Johnson', specialty: 'Cardiology' },
    { id: 3, name: 'Dr. Wilson', specialty: 'Dermatology' },
  ];

  const patients = [
    { id: 1, name: 'John Doe', phone: '+1-555-0123' },
    { id: 2, name: 'Jane Wilson', phone: '+1-555-0124' },
    { id: 3, name: 'Mike Brown', phone: '+1-555-0125' },
  ];

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Booking submitted:', formData);
    // Handle form submission
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  return (
    <div className="medx360-booking-form">
      <div className="medx360-form-header">
        <h2>New Booking</h2>
        <Link to="/booking" className="medx360-back-btn">← Back to Calendar</Link>
      </div>

      <form onSubmit={handleSubmit} className="medx360-form">
        <div className="medx360-form-grid">
          <div className="medx360-form-group">
            <label htmlFor="patient">Patient *</label>
            <select 
              id="patient" 
              name="patient" 
              value={formData.patient} 
              onChange={handleChange}
              required
            >
              <option value="">Select Patient</option>
              {patients.map(patient => (
                <option key={patient.id} value={patient.id}>
                  {patient.name} ({patient.phone})
                </option>
              ))}
            </select>
          </div>

          <div className="medx360-form-group">
            <label htmlFor="doctor">Doctor *</label>
            <select 
              id="doctor" 
              name="doctor" 
              value={formData.doctor} 
              onChange={handleChange}
              required
            >
              <option value="">Select Doctor</option>
              {doctors.map(doctor => (
                <option key={doctor.id} value={doctor.id}>
                  {doctor.name} - {doctor.specialty}
                </option>
              ))}
            </select>
          </div>

          <div className="medx360-form-group">
            <label htmlFor="date">Date *</label>
            <input 
              type="date" 
              id="date" 
              name="date" 
              value={formData.date} 
              onChange={handleChange}
              required
            />
          </div>

          <div className="medx360-form-group">
            <label htmlFor="time">Time *</label>
            <input 
              type="time" 
              id="time" 
              name="time" 
              value={formData.time} 
              onChange={handleChange}
              required
            />
          </div>

          <div className="medx360-form-group">
            <label htmlFor="duration">Duration (minutes)</label>
            <select 
              id="duration" 
              name="duration" 
              value={formData.duration} 
              onChange={handleChange}
            >
              <option value="15">15 minutes</option>
              <option value="30">30 minutes</option>
              <option value="45">45 minutes</option>
              <option value="60">60 minutes</option>
            </select>
          </div>

          <div className="medx360-form-group">
            <label htmlFor="status">Status</label>
            <select 
              id="status" 
              name="status" 
              value={formData.status} 
              onChange={handleChange}
            >
              <option value="confirmed">Confirmed</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>

        <div className="medx360-form-group">
          <label htmlFor="notes">Notes</label>
          <textarea 
            id="notes" 
            name="notes" 
            value={formData.notes} 
            onChange={handleChange}
            rows="4"
            placeholder="Additional notes for this appointment..."
          />
        </div>

        <div className="medx360-form-actions">
          <Link to="/booking" className="medx360-btn medx360-btn-secondary">
            Cancel
          </Link>
          <button type="submit" className="medx360-btn medx360-btn-primary">
            Create Booking
          </button>
        </div>
      </form>
    </div>
  );
};

export default BookingForm;
