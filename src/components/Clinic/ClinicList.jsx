import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import './ClinicList.css';

const ClinicList = () => {
  const [clinics, setClinics] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  // Load clinic data from localStorage
  useEffect(() => {
    const loadClinicData = () => {
      const savedClinics = localStorage.getItem('medx360_clinics');
      if (savedClinics) {
        setClinics(JSON.parse(savedClinics));
      } else {
        // Initialize with sample data if no data exists
        const sampleClinics = [
          {
            id: 1,
            name: 'Downtown Medical Center',
            type: 'General Practice',
            address: '123 Main Street, Downtown, NY 10001',
            phone: '+1 (555) 123-4567',
            email: 'info@downtownmedical.com',
            status: 'active',
            establishedDate: '2020-01-15',
            licenseNumber: 'CL001',
            services: ['General Consultation', 'Cardiology', 'Pediatrics'],
            staffCount: 12,
            patientCount: 1250
          },
          {
            id: 2,
            name: 'Westside Family Clinic',
            type: 'Family Practice',
            address: '456 Oak Avenue, Westside, NY 10002',
            phone: '+1 (555) 234-5678',
            email: 'contact@westsideclinic.com',
            status: 'active',
            establishedDate: '2018-06-20',
            licenseNumber: 'CL002',
            services: ['Family Medicine', 'Pediatrics', 'Geriatrics'],
            staffCount: 8,
            patientCount: 890
          },
          {
            id: 3,
            name: 'Northside Specialty Center',
            type: 'Specialty Practice',
            address: '789 Pine Road, Northside, NY 10003',
            phone: '+1 (555) 345-6789',
            email: 'admin@northsidespecialty.com',
            status: 'inactive',
            establishedDate: '2019-03-10',
            licenseNumber: 'CL003',
            services: ['Orthopedics', 'Neurology', 'Cardiology'],
            staffCount: 15,
            patientCount: 2100
          }
        ];
        setClinics(sampleClinics);
        localStorage.setItem('medx360_clinics', JSON.stringify(sampleClinics));
      }
      setLoading(false);
    };

    setTimeout(loadClinicData, 500);
  }, []);

  const handleDelete = (id) => {
    if (window.confirm('Are you sure you want to delete this clinic? This action cannot be undone.')) {
      const updatedClinics = clinics.filter(clinic => clinic.id !== id);
      setClinics(updatedClinics);
      localStorage.setItem('medx360_clinics', JSON.stringify(updatedClinics));
    }
  };

  const handleStatusToggle = (id) => {
    const updatedClinics = clinics.map(clinic => 
      clinic.id === id 
        ? { ...clinic, status: clinic.status === 'active' ? 'inactive' : 'active' }
        : clinic
    );
    setClinics(updatedClinics);
    localStorage.setItem('medx360_clinics', JSON.stringify(updatedClinics));
  };

  const filteredClinics = clinics.filter(clinic => {
    const matchesSearch = clinic.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         clinic.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         clinic.type.toLowerCase().includes(searchTerm.toLowerCase());
    return matchesSearch;
  });

  const getStatusBadge = (status) => {
    return status === 'active' ? '🟢 Active' : '🔴 Inactive';
  };

  const getTypeIcon = (type) => {
    const typeIcons = {
      'General Practice': '🏥',
      'Family Practice': '👨‍👩‍👧‍👦',
      'Specialty Practice': '🔬',
      'Dental Practice': '🦷',
      'Mental Health': '🧠'
    };
    return typeIcons[type] || '🏥';
  };

  if (loading) {
    return (
      <div className="medx360-clinic-list">
        <div className="medx360-loading">
          <div className="medx360-spinner"></div>
          <p>Loading clinics...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="medx360-clinic-list">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>Clinic Management</h1>
            <p>Manage your medical practice locations and facilities</p>
          </div>
          <div className="medx360-header-actions">
            <a
              href={getWordPressUrl(WORDPRESS_PAGES.CLINIC_NEW)}
              className="medx360-btn medx360-btn-primary"
            >
              <span>➕</span> Add New Clinic
            </a>
          </div>
        </div>
      </div>

      <div className="medx360-filters">
        <div className="medx360-search-box">
          <input
            type="text"
            placeholder="Search clinics by name, address, or type..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="medx360-search-input"
          />
          <span className="medx360-search-icon">🔍</span>
        </div>
      </div>

      <div className="medx360-stats-cards">
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">🏥</div>
          <div className="medx360-stat-content">
            <h3>{clinics.length}</h3>
            <p>Total Clinics</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">🟢</div>
          <div className="medx360-stat-content">
            <h3>{clinics.filter(c => c.status === 'active').length}</h3>
            <p>Active Clinics</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">👥</div>
          <div className="medx360-stat-content">
            <h3>{clinics.reduce((sum, clinic) => sum + clinic.staffCount, 0)}</h3>
            <p>Total Staff</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">👤</div>
          <div className="medx360-stat-content">
            <h3>{clinics.reduce((sum, clinic) => sum + clinic.patientCount, 0)}</h3>
            <p>Total Patients</p>
          </div>
        </div>
      </div>

      <div className="medx360-clinic-grid">
        {filteredClinics.map(clinic => (
          <div key={clinic.id} className="medx360-clinic-card">
            <div className="medx360-clinic-header">
              <div className="medx360-clinic-icon">
                {getTypeIcon(clinic.type)}
              </div>
              <div className="medx360-clinic-title">
                <h3>{clinic.name}</h3>
                <p className="medx360-clinic-type">{clinic.type}</p>
              </div>
              <div className="medx360-clinic-status">
                <span className={`medx360-status-badge ${clinic.status}`}>
                  {getStatusBadge(clinic.status)}
                </span>
              </div>
            </div>

            <div className="medx360-clinic-info">
              <div className="medx360-clinic-details">
                <div className="medx360-detail-item">
                  <span className="medx360-detail-icon">📍</span>
                  <span className="medx360-detail-text">{clinic.address}</span>
                </div>
                <div className="medx360-detail-item">
                  <span className="medx360-detail-icon">📞</span>
                  <span className="medx360-detail-text">{clinic.phone}</span>
                </div>
                <div className="medx360-detail-item">
                  <span className="medx360-detail-icon">📧</span>
                  <span className="medx360-detail-text">{clinic.email}</span>
                </div>
                <div className="medx360-detail-item">
                  <span className="medx360-detail-icon">📋</span>
                  <span className="medx360-detail-text">License: {clinic.licenseNumber}</span>
                </div>
              </div>

              <div className="medx360-clinic-services">
                <h4>Services Offered:</h4>
                <div className="medx360-services-tags">
                  {clinic.services.map((service, index) => (
                    <span key={index} className="medx360-service-tag">
                      {service}
                    </span>
                  ))}
                </div>
              </div>

              <div className="medx360-clinic-metrics">
                <div className="medx360-metric">
                  <span className="medx360-metric-value">{clinic.staffCount}</span>
                  <span className="medx360-metric-label">Staff</span>
                </div>
                <div className="medx360-metric">
                  <span className="medx360-metric-value">{clinic.patientCount}</span>
                  <span className="medx360-metric-label">Patients</span>
                </div>
                <div className="medx360-metric">
                  <span className="medx360-metric-value">
                    {new Date(clinic.establishedDate).getFullYear()}
                  </span>
                  <span className="medx360-metric-label">Established</span>
                </div>
              </div>
            </div>

            <div className="medx360-clinic-actions">
              <a
                href={getWordPressUrl(`clinic/edit/${clinic.id}`)}
                className="medx360-btn medx360-btn-secondary medx360-btn-sm"
              >
                ✏️ Edit
              </a>
              <button
                onClick={() => handleStatusToggle(clinic.id)}
                className={`medx360-btn medx360-btn-sm ${
                  clinic.status === 'active' ? 'medx360-btn-warning' : 'medx360-btn-success'
                }`}
              >
                {clinic.status === 'active' ? '⏸️ Deactivate' : '▶️ Activate'}
              </button>
              <button
                onClick={() => handleDelete(clinic.id)}
                className="medx360-btn medx360-btn-danger medx360-btn-sm"
              >
                🗑️ Delete
              </button>
            </div>
          </div>
        ))}
      </div>

      {filteredClinics.length === 0 && (
        <div className="medx360-empty-state">
          <div className="medx360-empty-icon">🏥</div>
          <h3>No clinics found</h3>
          <p>Try adjusting your search criteria or add new clinics to your practice.</p>
          <a
            href={getWordPressUrl(WORDPRESS_PAGES.CLINIC_NEW)}
            className="medx360-btn medx360-btn-primary"
          >
            Add First Clinic
          </a>
        </div>
      )}
    </div>
  );
};

export default ClinicList;
