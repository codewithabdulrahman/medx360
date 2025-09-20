import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import './ServiceList.css';

const ServiceList = () => {
  const [services, setServices] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterCategory, setFilterCategory] = useState('all');

  // Load service data from localStorage
  useEffect(() => {
    const loadServiceData = () => {
      const savedServices = localStorage.getItem('medx360_services');
      if (savedServices) {
        setServices(JSON.parse(savedServices));
      } else {
        // Initialize with sample data if no data exists
        const sampleServices = [
          {
            id: 1,
            name: 'General Consultation',
            category: 'General',
            duration: 30,
            price: 150.00,
            description: 'Comprehensive general health consultation and examination',
            status: 'active',
            createdDate: '2023-01-15',
            icon: '🩺',
            staffAssigned: ['Dr. Sarah Johnson', 'Dr. Michael Chen'],
            bookingCount: 245
          },
          {
            id: 2,
            name: 'Cardiology Consultation',
            category: 'Specialty',
            duration: 45,
            price: 250.00,
            description: 'Specialized heart and cardiovascular system examination',
            status: 'active',
            createdDate: '2023-01-20',
            icon: '❤️',
            staffAssigned: ['Dr. Sarah Johnson'],
            bookingCount: 89
          },
          {
            id: 3,
            name: 'Pediatric Check-up',
            category: 'Pediatrics',
            duration: 30,
            price: 180.00,
            description: 'Comprehensive health check-up for children',
            status: 'active',
            createdDate: '2023-02-01',
            icon: '👶',
            staffAssigned: ['Dr. Michael Chen'],
            bookingCount: 156
          },
          {
            id: 4,
            name: 'Physical Therapy Session',
            category: 'Therapy',
            duration: 60,
            price: 120.00,
            description: 'One-on-one physical therapy and rehabilitation session',
            status: 'inactive',
            createdDate: '2023-02-15',
            icon: '🏃‍♂️',
            staffAssigned: ['Therapist Emily Davis'],
            bookingCount: 67
          },
          {
            id: 5,
            name: 'Dental Cleaning',
            category: 'Dental',
            duration: 45,
            price: 100.00,
            description: 'Professional dental cleaning and oral hygiene check',
            status: 'active',
            createdDate: '2023-03-01',
            icon: '🦷',
            staffAssigned: ['Dr. Robert Wilson'],
            bookingCount: 98
          },
          {
            id: 6,
            name: 'Mental Health Counseling',
            category: 'Mental Health',
            duration: 50,
            price: 200.00,
            description: 'Individual counseling and mental health support session',
            status: 'active',
            createdDate: '2023-03-10',
            icon: '🧠',
            staffAssigned: ['Dr. Lisa Anderson'],
            bookingCount: 45
          }
        ];
        setServices(sampleServices);
        localStorage.setItem('medx360_services', JSON.stringify(sampleServices));
      }
      setLoading(false);
    };

    setTimeout(loadServiceData, 500);
  }, []);

  const handleDelete = (id) => {
    if (window.confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
      const updatedServices = services.filter(service => service.id !== id);
      setServices(updatedServices);
      localStorage.setItem('medx360_services', JSON.stringify(updatedServices));
    }
  };

  const handleStatusToggle = (id) => {
    const updatedServices = services.map(service => 
      service.id === id 
        ? { ...service, status: service.status === 'active' ? 'inactive' : 'active' }
        : service
    );
    setServices(updatedServices);
    localStorage.setItem('medx360_services', JSON.stringify(updatedServices));
  };

  const filteredServices = services.filter(service => {
    const matchesSearch = service.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         service.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         service.category.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesCategory = filterCategory === 'all' || service.category === filterCategory;
    return matchesSearch && matchesCategory;
  });

  const getStatusBadge = (status) => {
    return status === 'active' ? '🟢 Active' : '🔴 Inactive';
  };

  const getCategoryIcon = (category) => {
    const categoryIcons = {
      'General': '🩺',
      'Specialty': '🔬',
      'Pediatrics': '👶',
      'Therapy': '🏃‍♂️',
      'Dental': '🦷',
      'Mental Health': '🧠',
      'Emergency': '🚨',
      'Surgery': '⚕️'
    };
    return categoryIcons[category] || '🩺';
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(price);
  };

  const formatDuration = (minutes) => {
    if (minutes < 60) {
      return `${minutes} min`;
    } else {
      const hours = Math.floor(minutes / 60);
      const remainingMinutes = minutes % 60;
      return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`;
    }
  };

  if (loading) {
    return (
      <div className="medx360-service-list">
        <div className="medx360-loading">
          <div className="medx360-spinner"></div>
          <p>Loading services...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="medx360-service-list">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>Service Management</h1>
            <p>Manage the services offered by your medical practice</p>
          </div>
          <div className="medx360-header-actions">
            <a
              href={getWordPressUrl(WORDPRESS_PAGES.SERVICE_NEW)}
              className="medx360-btn medx360-btn-primary"
            >
              <span>➕</span> Add New Service
            </a>
          </div>
        </div>
      </div>

      <div className="medx360-filters">
        <div className="medx360-search-box">
          <input
            type="text"
            placeholder="Search services by name, description, or category..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="medx360-search-input"
          />
          <span className="medx360-search-icon">🔍</span>
        </div>
        
        <div className="medx360-filter-group">
          <label>Filter by Category:</label>
          <select
            value={filterCategory}
            onChange={(e) => setFilterCategory(e.target.value)}
            className="medx360-filter-select"
          >
            <option value="all">All Categories</option>
            <option value="General">General</option>
            <option value="Specialty">Specialty</option>
            <option value="Pediatrics">Pediatrics</option>
            <option value="Therapy">Therapy</option>
            <option value="Dental">Dental</option>
            <option value="Mental Health">Mental Health</option>
            <option value="Emergency">Emergency</option>
            <option value="Surgery">Surgery</option>
          </select>
        </div>
      </div>

      <div className="medx360-stats-cards">
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">🩺</div>
          <div className="medx360-stat-content">
            <h3>{services.length}</h3>
            <p>Total Services</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">🟢</div>
          <div className="medx360-stat-content">
            <h3>{services.filter(s => s.status === 'active').length}</h3>
            <p>Active Services</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">📅</div>
          <div className="medx360-stat-content">
            <h3>{services.reduce((sum, service) => sum + service.bookingCount, 0)}</h3>
            <p>Total Bookings</p>
          </div>
        </div>
        <div className="medx360-stat-card">
          <div className="medx360-stat-icon">💰</div>
          <div className="medx360-stat-content">
            <h3>{formatPrice(services.reduce((sum, service) => sum + (service.price * service.bookingCount), 0))}</h3>
            <p>Total Revenue</p>
          </div>
        </div>
      </div>

      <div className="medx360-service-grid">
        {filteredServices.map(service => (
          <div key={service.id} className="medx360-service-card">
            <div className="medx360-service-header">
              <div className="medx360-service-icon">
                {service.icon}
              </div>
              <div className="medx360-service-title">
                <h3>{service.name}</h3>
                <p className="medx360-service-category">
                  {getCategoryIcon(service.category)} {service.category}
                </p>
              </div>
              <div className="medx360-service-status">
                <span className={`medx360-status-badge ${service.status}`}>
                  {getStatusBadge(service.status)}
                </span>
              </div>
            </div>

            <div className="medx360-service-info">
              <div className="medx360-service-description">
                <p>{service.description}</p>
              </div>

              <div className="medx360-service-details">
                <div className="medx360-detail-row">
                  <span className="medx360-detail-label">Duration:</span>
                  <span className="medx360-detail-value">{formatDuration(service.duration)}</span>
                </div>
                <div className="medx360-detail-row">
                  <span className="medx360-detail-label">Price:</span>
                  <span className="medx360-detail-value medx360-price">{formatPrice(service.price)}</span>
                </div>
                <div className="medx360-detail-row">
                  <span className="medx360-detail-label">Bookings:</span>
                  <span className="medx360-detail-value">{service.bookingCount}</span>
                </div>
              </div>

              <div className="medx360-service-staff">
                <h4>Assigned Staff:</h4>
                <div className="medx360-staff-tags">
                  {service.staffAssigned.map((staff, index) => (
                    <span key={index} className="medx360-staff-tag">
                      👨‍⚕️ {staff}
                    </span>
                  ))}
                </div>
              </div>

              <div className="medx360-service-meta">
                <span className="medx360-created-date">
                  Created: {new Date(service.createdDate).toLocaleDateString()}
                </span>
              </div>
            </div>

            <div className="medx360-service-actions">
              <a
                href={getWordPressUrl(`service/edit/${service.id}`)}
                className="medx360-btn medx360-btn-secondary medx360-btn-sm"
              >
                ✏️ Edit
              </a>
              <button
                onClick={() => handleStatusToggle(service.id)}
                className={`medx360-btn medx360-btn-sm ${
                  service.status === 'active' ? 'medx360-btn-warning' : 'medx360-btn-success'
                }`}
              >
                {service.status === 'active' ? '⏸️ Deactivate' : '▶️ Activate'}
              </button>
              <button
                onClick={() => handleDelete(service.id)}
                className="medx360-btn medx360-btn-danger medx360-btn-sm"
              >
                🗑️ Delete
              </button>
            </div>
          </div>
        ))}
      </div>

      {filteredServices.length === 0 && (
        <div className="medx360-empty-state">
          <div className="medx360-empty-icon">🩺</div>
          <h3>No services found</h3>
          <p>Try adjusting your search criteria or add new services to your practice.</p>
          <a
            href={getWordPressUrl(WORDPRESS_PAGES.SERVICE_NEW)}
            className="medx360-btn medx360-btn-primary"
          >
            Add First Service
          </a>
        </div>
      )}
    </div>
  );
};

export default ServiceList;
