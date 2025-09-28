import React, { useState } from 'react';
import { 
  Building2, 
  Plus, 
  Search, 
  Filter, 
  Edit, 
  Trash2, 
  Eye,
  MapPin,
  Phone,
  Mail,
  Globe
} from 'lucide-react';
import { useClinics, useCreateClinic, useUpdateClinic, useDeleteClinic } from '@hooks/useApi';
import { 
  FormInput, 
  FormButton, 
  FormCard, 
  FormLoading, 
  FormStatus,
  FormSelect 
} from '@components/forms';

const ClinicCard = ({ clinic, onEdit, onDelete, onView }) => {
  const statusColors = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
  };

  return (
    <div className="bg-white shadow rounded-lg p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <div className="flex items-center">
            <Building2 className="h-8 w-8 text-blue-600 mr-3" />
            <div>
              <h3 className="text-lg font-semibold text-gray-900">{clinic.name}</h3>
              <p className="text-sm text-gray-600">{clinic.description}</p>
            </div>
          </div>
          
          <div className="mt-4 space-y-2">
            {clinic.address && (
              <div className="flex items-center text-sm text-gray-600">
                <MapPin className="h-4 w-4 mr-2" />
                {clinic.address}, {clinic.city}, {clinic.state}
              </div>
            )}
            {clinic.phone && (
              <div className="flex items-center text-sm text-gray-600">
                <Phone className="h-4 w-4 mr-2" />
                {clinic.phone}
              </div>
            )}
            {clinic.email && (
              <div className="flex items-center text-sm text-gray-600">
                <Mail className="h-4 w-4 mr-2" />
                {clinic.email}
              </div>
            )}
            {clinic.website && (
              <div className="flex items-center text-sm text-gray-600">
                <Globe className="h-4 w-4 mr-2" />
                <a href={clinic.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800">
                  {clinic.website}
                </a>
              </div>
            )}
          </div>
        </div>
        
        <div className="flex items-center space-x-2 ml-4">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[clinic.status]}`}>
            {clinic.status}
          </span>
        </div>
      </div>
      
      <div className="mt-6 flex items-center justify-between">
        <div className="text-sm text-gray-500">
          Created {new Date(clinic.created_at).toLocaleDateString()}
        </div>
        <div className="flex items-center space-x-2">
          <button
            onClick={() => onView(clinic)}
            className="p-2 text-gray-400 hover:text-gray-600"
            title="View Details"
          >
            <Eye className="h-4 w-4" />
          </button>
          <button
            onClick={() => onEdit(clinic)}
            className="p-2 text-gray-400 hover:text-blue-600"
            title="Edit Clinic"
          >
            <Edit className="h-4 w-4" />
          </button>
          <button
            onClick={() => onDelete(clinic)}
            className="p-2 text-gray-400 hover:text-red-600"
            title="Delete Clinic"
          >
            <Trash2 className="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  );
};

const ClinicForm = ({ clinic, onSave, onCancel, isOpen, isLoading }) => {
  const [formData, setFormData] = useState({
    name: clinic?.name || '',
    slug: clinic?.slug || '',
    description: clinic?.description || '',
    address: clinic?.address || '',
    city: clinic?.city || '',
    state: clinic?.state || '',
    country: clinic?.country || '',
    postal_code: clinic?.postal_code || '',
    phone: clinic?.phone || '',
    email: clinic?.email || '',
    website: clinic?.website || '',
    status: clinic?.status || 'active',
  });

  const [errors, setErrors] = useState({});

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.name.trim()) {
      newErrors.name = 'Clinic name is required';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required';
    }
    
    if (!formData.address.trim()) {
      newErrors.address = 'Address is required';
    }
    
    if (!formData.city.trim()) {
      newErrors.city = 'City is required';
    }
    
    if (!formData.state.trim()) {
      newErrors.state = 'State is required';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validateForm()) {
      onSave(formData);
    }
  };

  const handleChange = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    if (errors[field]) {
      setErrors(prev => ({ ...prev, [field]: null }));
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div className="mt-3">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-medium text-gray-900">
              {clinic ? 'Edit Clinic' : 'Add New Clinic'}
            </h3>
            <button
              onClick={onCancel}
              className="text-gray-400 hover:text-gray-600"
            >
              <span className="sr-only">Close</span>
              <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormInput
                label="Clinic Name"
                value={formData.name}
                onChange={(e) => handleChange('name', e.target.value)}
                error={errors.name}
                required
              />
              
              <FormInput
                label="Slug"
                value={formData.slug}
                onChange={(e) => handleChange('slug', e.target.value)}
                error={errors.slug}
                placeholder="clinic-name"
              />
              
              <FormInput
                label="Email"
                type="email"
                value={formData.email}
                onChange={(e) => handleChange('email', e.target.value)}
                error={errors.email}
                required
              />
              
              <FormInput
                label="Phone"
                value={formData.phone}
                onChange={(e) => handleChange('phone', e.target.value)}
                error={errors.phone}
                required
              />
              
              <FormInput
                label="Address"
                value={formData.address}
                onChange={(e) => handleChange('address', e.target.value)}
                error={errors.address}
                required
              />
              
              <FormInput
                label="City"
                value={formData.city}
                onChange={(e) => handleChange('city', e.target.value)}
                error={errors.city}
                required
              />
              
              <FormInput
                label="State"
                value={formData.state}
                onChange={(e) => handleChange('state', e.target.value)}
                error={errors.state}
                required
              />
              
              <FormInput
                label="Country"
                value={formData.country}
                onChange={(e) => handleChange('country', e.target.value)}
                error={errors.country}
              />
              
              <FormInput
                label="Postal Code"
                value={formData.postal_code}
                onChange={(e) => handleChange('postal_code', e.target.value)}
                error={errors.postal_code}
              />
              
              <FormInput
                label="Website"
                value={formData.website}
                onChange={(e) => handleChange('website', e.target.value)}
                error={errors.website}
                placeholder="https://example.com"
              />
              
              <FormSelect
                label="Status"
                value={formData.status}
                onChange={(e) => handleChange('status', e.target.value)}
                options={[
                  { value: 'active', label: 'Active' },
                  { value: 'inactive', label: 'Inactive' },
                  { value: 'pending', label: 'Pending' },
                ]}
                error={errors.status}
              />
            </div>
            
            <FormInput
              label="Description"
              value={formData.description}
              onChange={(e) => handleChange('description', e.target.value)}
              error={errors.description}
              placeholder="Brief description of the clinic"
            />

            <div className="flex items-center justify-end space-x-3 pt-4 border-t">
              <FormButton
                type="button"
                variant="outline"
                onClick={onCancel}
              >
                Cancel
              </FormButton>
              <FormButton type="submit" loading={isLoading}>
                {clinic ? 'Update Clinic' : 'Create Clinic'}
              </FormButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

const Clinics = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingClinic, setEditingClinic] = useState(null);

  const { data: clinicsResponse, isLoading, error } = useClinics();
  const createClinicMutation = useCreateClinic();
  const updateClinicMutation = useUpdateClinic();
  const deleteClinicMutation = useDeleteClinic();

  const clinics = clinicsResponse?.data || [];

  const filteredClinics = clinics.filter(clinic => {
    const matchesSearch = clinic.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         clinic.city.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         clinic.email.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = !statusFilter || clinic.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  const handleEdit = (clinic) => {
    setEditingClinic(clinic);
    setShowForm(true);
  };

  const handleDelete = async (clinic) => {
    if (window.confirm(`Are you sure you want to delete "${clinic.name}"?`)) {
      try {
        await deleteClinicMutation.mutateAsync(clinic.id);
      } catch (error) {
        console.error('Failed to delete clinic:', error);
      }
    }
  };

  const handleView = (clinic) => {
    // TODO: Implement view details modal
    console.log('View clinic:', clinic);
  };

  const handleSave = async (formData) => {
    try {
      if (editingClinic) {
        await updateClinicMutation.mutateAsync({ 
          id: editingClinic.id, 
          data: formData 
        });
      } else {
        await createClinicMutation.mutateAsync(formData);
      }
      setShowForm(false);
      setEditingClinic(null);
    } catch (error) {
      console.error('Failed to save clinic:', error);
    }
  };

  const handleCancel = () => {
    setShowForm(false);
    setEditingClinic(null);
  };

  if (isLoading) {
    return <FormLoading message="Loading clinics..." />;
  }

  if (error) {
    return (
      <FormStatus 
        type="error" 
        message="Failed to load clinics. Please try again." 
      />
    );
  }

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Clinics</h1>
          <p className="mt-1 text-sm text-gray-600">
            Manage your medical clinics and facilities
          </p>
        </div>
        <FormButton onClick={() => setShowForm(true)}>
          <Plus className="h-4 w-4 mr-2" />
          Add Clinic
        </FormButton>
      </div>

      {/* Filters */}
      <div className="bg-white shadow rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormInput
            placeholder="Search clinics..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            icon={Search}
          />
          <FormSelect
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            options={[
              { value: '', label: 'All Statuses' },
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
              { value: 'pending', label: 'Pending' },
            ]}
          />
          <FormButton variant="outline">
            <Filter className="h-4 w-4 mr-2" />
            More Filters
          </FormButton>
        </div>
      </div>

      {/* Clinics Grid */}
      {filteredClinics.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <Building2 className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No clinics found</h3>
            <p className="mt-1 text-sm text-gray-500">
              {searchTerm || statusFilter 
                ? 'Try adjusting your search criteria.' 
                : 'Get started by creating your first clinic.'
              }
            </p>
            {!searchTerm && !statusFilter && (
              <div className="mt-6">
                <FormButton onClick={() => setShowForm(true)}>
                  <Plus className="h-4 w-4 mr-2" />
                  Add First Clinic
                </FormButton>
              </div>
            )}
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {filteredClinics.map((clinic) => (
            <ClinicCard
              key={clinic.id}
              clinic={clinic}
              onEdit={handleEdit}
              onDelete={handleDelete}
              onView={handleView}
            />
          ))}
        </div>
      )}

      {/* Clinic Form Modal */}
      <ClinicForm
        clinic={editingClinic}
        onSave={handleSave}
        onCancel={handleCancel}
        isOpen={showForm}
        isLoading={createClinicMutation.isLoading || updateClinicMutation.isLoading}
      />
    </div>
  );
};

export default Clinics;