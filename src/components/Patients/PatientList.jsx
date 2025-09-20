import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import { patientAPI } from '../../utils/api';
import { useToast } from '../Shared/ToastContext';
import './PatientList.css';

const PatientList = () => {
  const [patients, setPatients] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('all');
  const [pagination, setPagination] = useState({ page: 1, per_page: 20, total: 0 });
  const { showToast } = useToast();

  // Load patient data from API
  useEffect(() => {
    loadPatientData();
  }, [pagination.page, searchTerm, filterStatus]);

  const loadPatientData = async () => {
    try {
      setLoading(true);
      
      const params = {
        page: pagination.page,
        per_page: pagination.per_page,
        search: searchTerm || undefined,
        status: filterStatus === 'all' ? undefined : filterStatus
      };

      const response = await patientAPI.getAll(params);
      
      setPatients(response.data || []);
      setPagination(prev => ({
        ...prev,
        total: response.total || 0,
        total_pages: response.total_pages || 1
      }));
      
    } catch (error) {
      console.error('Error loading patient data:', error);
      showToast('Failed to load patient data', 'error');
      setPatients([]);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this patient?')) {
      try {
        await patientAPI.delete(id);
        showToast('Patient deleted successfully', 'success');
        loadPatientData(); // Reload data
      } catch (error) {
        console.error('Error deleting patient:', error);
        showToast('Failed to delete patient', 'error');
      }
    }
  };

  const handleStatusToggle = async (id) => {
    try {
      const patient = patients.find(p => p.id === id);
      const newStatus = patient.status === 'active' ? 'inactive' : 'active';
      
      await patientAPI.update(id, { status: newStatus });
      showToast(`Patient ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully`, 'success');
      loadPatientData(); // Reload data
    } catch (error) {
      console.error('Error updating patient status:', error);
      showToast('Failed to update patient status', 'error');
    }
  };

  const getStatusBadge = (status) => {
    const statusConfig = {
      active: { text: '🟢 Active', class: 'active' },
      inactive: { text: '🔴 Inactive', class: 'inactive' },
      archived: { text: '📁 Archived', class: 'archived' }
    };
    return statusConfig[status] || statusConfig.active;
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
  };

  if (loading) {
    return (
      <div className="medx360-patient-list">
        <div className="medx360-loading">
          <div className="medx360-spinner"></div>
          <p>Loading patients...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="medx360-patient-list">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>Patient Management</h1>
            <p>Manage your patient records and information</p>
          </div>
          <div className="medx360-header-actions">
            <a
              href={getWordPressUrl(WORDPRESS_PAGES.PATIENTS_NEW)}
              className="medx360-btn medx360-btn-primary"
            >
              <span>➕</span> Add Patient
            </a>
          </div>
        </div>
      </div>

      <div className="medx360-filters">
        <div className="medx360-search-box">
          <input
            type="text"
            placeholder="Search patients by name, email, or phone..."
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
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>

      <div className="medx360-stats-cards">
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">👥</div>
          <div className="medx360-stat-content">
            <h3>{pagination.total}</h3>
            <p>Total Patients</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">🟢</div>
          <div className="medx360-stat-content">
            <h3>{patients.filter(p => p.status === 'active').length}</h3>
            <p>Active</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">🔴</div>
          <div className="medx360-stat-content">
            <h3>{patients.filter(p => p.status === 'inactive').length}</h3>
            <p>Inactive</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">📁</div>
          <div className="medx360-stat-content">
            <h3>{patients.filter(p => p.status === 'archived').length}</h3>
            <p>Archived</p>
          </div>
        </div>
      </div>

      <div className="medx360-patient-grid">
        {patients.map(patient => {
          const statusBadge = getStatusBadge(patient.status);
          return (
            <div key={patient.id} className="medx360-patient-card">
              <div className="medx360-patient-avatar">
                {patient.gender === 'male' ? '👨' : patient.gender === 'female' ? '👩' : '👤'}
              </div>
              <div className="medx360-patient-info">
                <h3>{patient.first_name} {patient.last_name}</h3>
                <p className="medx360-patient-email">📧 {patient.email}</p>
                <p className="medx360-patient-phone">📞 {patient.phone || 'N/A'}</p>
                {patient.date_of_birth && (
                  <p className="medx360-patient-dob">🎂 {formatDate(patient.date_of_birth)}</p>
                )}
                <div className="medx360-patient-meta">
                  <span className={`medx360-status-badge ${statusBadge.class}`}>
                    {statusBadge.text}
                  </span>
                  <span className="medx360-register-date">
                    Registered: {formatDate(patient.created_at)}
                  </span>
                </div>
              </div>
              <div className="medx360-patient-actions">
                <a
                  href={getWordPressUrl(`patients/profile/${patient.id}`)}
                  className="medx360-btn medx360-btn-secondary medx360-btn-sm"
                >
                  👁️ View
                </a>
                <a
                  href={getWordPressUrl(`patients/edit/${patient.id}`)}
                  className="medx360-btn medx360-btn-secondary medx360-btn-sm"
                >
                  ✏️ Edit
                </a>
                <button
                  onClick={() => handleStatusToggle(patient.id)}
                  className={`medx360-btn medx360-btn-sm ${
                    patient.status === 'active' ? 'medx360-btn-warning' : 'medx360-btn-success'
                  }`}
                >
                  {patient.status === 'active' ? '⏸️ Deactivate' : '▶️ Activate'}
                </button>
                <button
                  onClick={() => handleDelete(patient.id)}
                  className="medx360-btn medx360-btn-danger medx360-btn-sm"
                >
                  🗑️ Delete
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {patients.length === 0 && !loading && (
        <div className="medx360-empty-state">
          <div className="medx360-empty-icon">👥</div>
          <h3>No patients found</h3>
          <p>Try adjusting your search criteria or add new patients.</p>
          <a
            href={getWordPressUrl(WORDPRESS_PAGES.PATIENTS_NEW)}
            className="medx360-btn medx360-btn-primary"
          >
            Add First Patient
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

export default PatientList;
