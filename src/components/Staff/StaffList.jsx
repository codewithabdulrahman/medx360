import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import { staffAPI } from '../../utils/api';
import { useToast } from '../Shared/ToastContext';
import './StaffList.css';

const StaffList = () => {
  const [staff, setStaff] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterRole, setFilterRole] = useState('all');
  const [showAddModal, setShowAddModal] = useState(false);
  const [editingStaff, setEditingStaff] = useState(null);
  const [pagination, setPagination] = useState({ page: 1, per_page: 20, total: 0 });
  const { showToast } = useToast();

  // Load staff data from API
  useEffect(() => {
    loadStaffData();
  }, [pagination.page, searchTerm, filterRole]);

  const loadStaffData = async () => {
    try {
      setLoading(true);
      
      const params = {
        page: pagination.page,
        per_page: pagination.per_page,
        search: searchTerm || undefined,
        status: filterRole === 'all' ? undefined : filterRole
      };

      const response = await staffAPI.getAll(params);
      
      // Transform API data to match component structure
      const transformedStaff = response.data?.map(member => ({
        id: member.id,
        name: `${member.first_name} ${member.last_name}`,
        role: member.specialty ? 'doctor' : 'staff', // Map based on specialty
        specialization: member.specialty || 'General',
        email: member.email,
        phone: member.phone || 'N/A',
        status: member.status,
        joinDate: member.hire_date || member.created_at,
        avatar: member.specialty ? '👨‍⚕️' : '👩‍⚕️'
      })) || [];

      setStaff(transformedStaff);
      setPagination(prev => ({
        ...prev,
        total: response.total || 0,
        total_pages: response.total_pages || 1
      }));
      
    } catch (error) {
      console.error('Error loading staff data:', error);
      showToast('Failed to load staff data', 'error');
      setStaff([]);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this staff member?')) {
      try {
        await staffAPI.delete(id);
        showToast('Staff member deleted successfully', 'success');
        loadStaffData(); // Reload data
      } catch (error) {
        console.error('Error deleting staff member:', error);
        showToast('Failed to delete staff member', 'error');
      }
    }
  };

  const handleStatusToggle = async (id) => {
    try {
      const member = staff.find(s => s.id === id);
      const newStatus = member.status === 'active' ? 'inactive' : 'active';
      
      await staffAPI.update(id, { status: newStatus });
      showToast(`Staff member ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully`, 'success');
      loadStaffData(); // Reload data
    } catch (error) {
      console.error('Error updating staff status:', error);
      showToast('Failed to update staff status', 'error');
    }
  };

  // Since we're now filtering on the server side, we don't need client-side filtering
  // But we'll keep this for any additional client-side filtering if needed
  const filteredStaff = staff;

  const getRoleLabel = (role) => {
    const roleLabels = {
      doctor: 'Doctor',
      nurse: 'Nurse',
      therapist: 'Therapist',
      specialist: 'Specialist',
      assistant: 'Assistant',
      admin: 'Administrator'
    };
    return roleLabels[role] || role;
  };

  const getStatusBadge = (status) => {
    return status === 'active' ? '🟢 Active' : '🔴 Inactive';
  };

  if (loading) {
    return (
      <div className="medx360-staff-list">
        <div className="medx360-loading">
          <div className="medx360-spinner"></div>
          <p>Loading staff members...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="medx360-staff-list">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>Staff Management</h1>
            <p>Manage your medical team and practitioners</p>
          </div>
          <div className="medx360-header-actions">
            <a
              href={getWordPressUrl(WORDPRESS_PAGES.STAFF_NEW)}
              className="medx360-btn medx360-btn-primary"
            >
              <span>➕</span> Add Staff Member
            </a>
          </div>
        </div>
      </div>

      <div className="medx360-filters">
        <div className="medx360-search-box">
          <input
            type="text"
            placeholder="Search staff by name, email, or specialization..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="medx360-search-input"
          />
          <span className="medx360-search-icon">🔍</span>
        </div>
        
        <div className="medx360-filter-group">
          <label>Filter by Role:</label>
          <select
            value={filterRole}
            onChange={(e) => setFilterRole(e.target.value)}
            className="medx360-filter-select"
          >
            <option value="all">All Roles</option>
            <option value="doctor">Doctors</option>
            <option value="nurse">Nurses</option>
            <option value="therapist">Therapists</option>
            <option value="specialist">Specialists</option>
            <option value="assistant">Assistants</option>
            <option value="admin">Administrators</option>
          </select>
        </div>
      </div>

      <div className="medx360-stats-cards">
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">👥</div>
          <div className="medx360-stat-content">
            <h3>{staff.length}</h3>
            <p>Total Staff</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">👨‍⚕️</div>
          <div className="medx360-stat-content">
            <h3>{staff.filter(s => s.role === 'doctor').length}</h3>
            <p>Doctors</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">👩‍⚕️</div>
          <div className="medx360-stat-content">
            <h3>{staff.filter(s => s.role === 'nurse').length}</h3>
            <p>Nurses</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">🟢</div>
          <div className="medx360-stat-content">
            <h3>{staff.filter(s => s.status === 'active').length}</h3>
            <p>Active</p>
          </div>
        </div>
      </div>

      <div className="medx360-staff-grid">
        {filteredStaff.map(member => (
          <div key={member.id} className="medx360-staff-card">
            <div className="medx360-staff-avatar">
              {member.avatar}
            </div>
            <div className="medx360-staff-info">
              <h3>{member.name}</h3>
              <p className="medx360-staff-role">{getRoleLabel(member.role)}</p>
              <p className="medx360-staff-specialization">{member.specialization}</p>
              <div className="medx360-staff-contact">
                <p>📧 {member.email}</p>
                <p>📞 {member.phone}</p>
              </div>
              <div className="medx360-staff-meta">
                <span className={`medx360-status-badge ${member.status}`}>
                  {getStatusBadge(member.status)}
                </span>
                <span className="medx360-join-date">
                  Joined: {new Date(member.joinDate).toLocaleDateString()}
                </span>
              </div>
            </div>
            <div className="medx360-staff-actions">
              <a
                href={getWordPressUrl(`staff/edit/${member.id}`)}
                className="medx360-btn medx360-btn-secondary medx360-btn-sm"
              >
                ✏️ Edit
              </a>
              <button
                onClick={() => handleStatusToggle(member.id)}
                className={`medx360-btn medx360-btn-sm ${
                  member.status === 'active' ? 'medx360-btn-warning' : 'medx360-btn-success'
                }`}
              >
                {member.status === 'active' ? '⏸️ Deactivate' : '▶️ Activate'}
              </button>
              <button
                onClick={() => handleDelete(member.id)}
                className="medx360-btn medx360-btn-danger medx360-btn-sm"
              >
                🗑️ Delete
              </button>
            </div>
          </div>
        ))}
      </div>

      {filteredStaff.length === 0 && (
        <div className="medx360-empty-state">
          <div className="medx360-empty-icon">👥</div>
          <h3>No staff members found</h3>
          <p>Try adjusting your search criteria or add new staff members.</p>
          <a
            href={getWordPressUrl(WORDPRESS_PAGES.STAFF_NEW)}
            className="medx360-btn medx360-btn-primary"
          >
            Add First Staff Member
          </a>
        </div>
      )}
    </div>
  );
};

export default StaffList;
