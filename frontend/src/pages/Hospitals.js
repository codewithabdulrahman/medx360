import React, { useState } from 'react';
import { 
  Building, 
  Plus, 
  Search, 
  Filter, 
  Edit, 
  Trash2, 
  Eye,
  MapPin,
  Phone,
  Mail,
  Globe,
  Users
} from 'lucide-react';
import { useHospitals, useCreateHospital, useUpdateHospital, useDeleteHospital, useClinics } from '@hooks/useApi';
import { useToast } from '@components/Toast';
import { 
  FormInput, 
  FormButton, 
  FormCard, 
  FormLoading, 
  FormStatus,
  FormSelect,
  FormTextarea 
} from '@components/forms';
import Modal from '@components/Modal';
import ConfirmationModal from '@components/ConfirmationModal';

const HospitalCard = ({ hospital, onEdit, onDelete, onView }) => {
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
            <Building className="h-8 w-8 text-green-600 mr-3" />
            <div>
              <h3 className="text-lg font-semibold text-gray-900">{hospital.name}</h3>
              <p className="text-sm text-gray-600">{hospital.description}</p>
            </div>
          </div>
          
          <div className="mt-4 space-y-2">
            {hospital.address && (
              <div className="flex items-center text-sm text-gray-600">
                <MapPin className="h-4 w-4 mr-2" />
                {hospital.address}, {hospital.city}, {hospital.state}
              </div>
            )}
            {hospital.phone && (
              <div className="flex items-center text-sm text-gray-600">
                <Phone className="h-4 w-4 mr-2" />
                {hospital.phone}
              </div>
            )}
            {hospital.email && (
              <div className="flex items-center text-sm text-gray-600">
                <Mail className="h-4 w-4 mr-2" />
                {hospital.email}
              </div>
            )}
            {hospital.website && (
              <div className="flex items-center text-sm text-gray-600">
                <Globe className="h-4 w-4 mr-2" />
                <a href={hospital.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800">
                  {hospital.website}
                </a>
              </div>
            )}
            {hospital.capacity && (
              <div className="flex items-center text-sm text-gray-600">
                <Users className="h-4 w-4 mr-2" />
                Capacity: {hospital.capacity} beds
              </div>
            )}
          </div>
        </div>
        
        <div className="flex items-center space-x-2 ml-4">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[hospital.status]}`}>
            {hospital.status}
          </span>
        </div>
      </div>
      
      <div className="mt-6 flex items-center justify-between">
        <div className="text-sm text-gray-500">
          Created {new Date(hospital.created_at).toLocaleDateString()}
        </div>
        <div className="flex items-center space-x-2">
          <button
            onClick={() => onView(hospital)}
            className="p-2 text-gray-400 hover:text-gray-600"
            title="View Details"
          >
            <Eye className="h-4 w-4" />
          </button>
          <button
            onClick={() => onEdit(hospital)}
            className="p-2 text-gray-400 hover:text-blue-600"
            title="Edit Hospital"
          >
            <Edit className="h-4 w-4" />
          </button>
          <button
            onClick={() => onDelete(hospital)}
            className="p-2 text-gray-400 hover:text-red-600"
            title="Delete Hospital"
          >
            <Trash2 className="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  );
};

const HospitalForm = ({ hospital, onSave, onCancel, isOpen, isLoading, submitError }) => {
  const { addToast } = useToast();
  const { data: clinicsData } = useClinics();
  const clinics = clinicsData?.data || [];
  
  const [formData, setFormData] = useState({
    clinic_id: hospital?.clinic_id || '',
    name: hospital?.name || '',
    slug: hospital?.slug || '',
    description: hospital?.description || '',
    address: hospital?.address || '',
    city: hospital?.city || '',
    state: hospital?.state || '',
    country: hospital?.country || '',
    postal_code: hospital?.postal_code || '',
    phone: hospital?.phone || '',
    email: hospital?.email || '',
    website: hospital?.website || '',
    capacity: hospital?.capacity || '',
    specialties: hospital?.specialties || '',
    status: hospital?.status || 'active',
  });

  const [errors, setErrors] = useState({});

  // Reset form when hospital or modal open state changes
  React.useEffect(() => {
    if (!isOpen) return;

    if (hospital) {
      setFormData({
        clinic_id: hospital.clinic_id || '',
        name: hospital.name || '',
        slug: hospital.slug || '',
        description: hospital.description || '',
        address: hospital.address || '',
        city: hospital.city || '',
        state: hospital.state || '',
        country: hospital.country || '',
        postal_code: hospital.postal_code || '',
        phone: hospital.phone || '',
        email: hospital.email || '',
        website: hospital.website || '',
        capacity: hospital.capacity || '',
        specialties: hospital.specialties || '',
        status: hospital.status || 'active',
      });
    } else {
      setFormData({
        clinic_id: '',
        name: '',
        slug: '',
        description: '',
        address: '',
        city: '',
        state: '',
        country: '',
        postal_code: '',
        phone: '',
        email: '',
        website: '',
        capacity: '',
        specialties: '',
        status: 'active',
      });
    }

    setErrors({});
  }, [hospital, isOpen]);

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.clinic_id) {
      newErrors.clinic_id = 'Please select a clinic';
    }
    
    if (!formData.name.trim()) {
      newErrors.name = 'Hospital name is required';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required';
    } else if (!/^[\+]?[1-9][\d]{0,15}$/.test(formData.phone.replace(/[\s\-\(\)]/g, ''))) {
      newErrors.phone = 'Invalid phone number';
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

    if (formData.slug && !/^[a-z0-9\-]+$/.test(formData.slug)) {
      newErrors.slug = 'Slug must contain only lowercase letters, numbers, and hyphens';
    }

    if (formData.website && !/^https?:\/\/.+\..+/.test(formData.website)) {
      newErrors.website = 'Invalid website URL';
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

  const clinicOptions = clinics.map(clinic => ({
    value: clinic.id,
    label: clinic.name
  }));

  return (
    <Modal
      isOpen={isOpen}
      onClose={onCancel}
      title={hospital ? 'Edit Hospital' : 'Add New Hospital'}
      size="lg"
    >
      <form onSubmit={handleSubmit} className="space-y-6" noValidate>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormSelect
            label="Clinic"
            value={formData.clinic_id}
            onChange={(e) => handleChange('clinic_id', e.target.value)}
            options={clinicOptions}
            error={errors.clinic_id}
            required
          />
          
          <FormInput
            label="Hospital Name"
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
            placeholder="hospital-name"
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
          
          <FormInput
            label="Capacity"
            type="number"
            value={formData.capacity}
            onChange={(e) => handleChange('capacity', e.target.value)}
            error={errors.capacity}
            placeholder="Number of beds"
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
        
        <FormTextarea
          label="Description"
          value={formData.description}
          onChange={(e) => handleChange('description', e.target.value)}
          error={errors.description}
          placeholder="Brief description of the hospital"
        />

        <FormInput
          label="Specialties"
          value={formData.specialties}
          onChange={(e) => handleChange('specialties', e.target.value)}
          error={errors.specialties}
          placeholder="Cardiology, Neurology, etc. (comma separated)"
        />

        {/* Submit Error Display - for server/API errors */}
        {submitError && (
          <div className="bg-red-50 border border-red-200 rounded-md p-4">
            <div className="flex">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-red-800">Validation Error</h3>
                <div className="mt-2 text-sm text-red-700">{submitError}</div>
              </div>
            </div>
          </div>
        )}

        <div className="flex items-center justify-end space-x-3 pt-4 border-t">
          <FormButton
            type="button"
            variant="outline"
            onClick={onCancel}
          >
            Cancel
          </FormButton>
          <FormButton type="submit" loading={isLoading}>
            {hospital ? 'Update Hospital' : 'Create Hospital'}
          </FormButton>
        </div>
      </form>
    </Modal>
  );
};

const Hospitals = () => {
  const { addToast } = useToast();
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [clinicFilter, setClinicFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingHospital, setEditingHospital] = useState(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [hospitalToDelete, setHospitalToDelete] = useState(null);
  const [submitError, setSubmitError] = useState('');

  const { data: hospitalsResponse, isLoading, error } = useHospitals();
  const { data: clinicsResponse } = useClinics();
  const createHospitalMutation = useCreateHospital();
  const updateHospitalMutation = useUpdateHospital();
  const deleteHospitalMutation = useDeleteHospital();

  const hospitals = hospitalsResponse?.data || [];
  const clinics = clinicsResponse?.data || [];

  const filteredHospitals = hospitals.filter(hospital => {
    const matchesSearch = (hospital.name?.toLowerCase() || '').includes(searchTerm.toLowerCase()) ||
                         (hospital.city?.toLowerCase() || '').includes(searchTerm.toLowerCase()) ||
                         (hospital.email?.toLowerCase() || '').includes(searchTerm.toLowerCase());
    const matchesStatus = !statusFilter || hospital.status === statusFilter;
    const matchesClinic = !clinicFilter || hospital.clinic_id === parseInt(clinicFilter);
    return matchesSearch && matchesStatus && matchesClinic;
  });

  const handleEdit = (hospital) => {
    setEditingHospital(hospital);
    setSubmitError('');
    setShowForm(true);
  };

  const handleDelete = (hospital) => {
    setHospitalToDelete(hospital);
    setShowDeleteConfirm(true);
  };

  const confirmDelete = async () => {
    if (!hospitalToDelete) return;
    
    try {
      await deleteHospitalMutation.mutateAsync(hospitalToDelete.id);
      addToast({ type: 'success', title: 'Deleted', message: 'Hospital deleted successfully' });
      setShowDeleteConfirm(false);
      setHospitalToDelete(null);
    } catch (error) {
      console.error('Failed to delete hospital:', error);
      addToast({ type: 'error', title: 'Error', message: 'Failed to delete hospital. Please try again.' });
    }
  };

  const cancelDelete = () => {
    setShowDeleteConfirm(false);
    setHospitalToDelete(null);
  };

  const handleView = (hospital) => {
    // TODO: Implement view details modal
    addToast({ type: 'info', title: 'Info', message: `Viewing hospital: ${hospital.name}` });
  };

  const handleSave = async (formData) => {
    try {
      if (editingHospital) {
        await updateHospitalMutation.mutateAsync({ 
          id: editingHospital.id, 
          data: formData 
        });
        addToast({ type: 'success', title: 'Updated', message: 'Hospital updated successfully' });
      } else {
        await createHospitalMutation.mutateAsync(formData);
        addToast({ type: 'success', title: 'Created', message: 'Hospital created successfully' });
      }
      setShowForm(false);
      setEditingHospital(null);
      setSubmitError('');
    } catch (error) {
      console.error('Failed to save hospital:', error);
      
      // Show detailed validation errors if available
      if (error.message && error.message !== 'Request failed') {
        addToast({ type: 'error', title: '', message: error.message });
        setSubmitError(error.message);
      } else {
        addToast({ type: 'error', title: 'Error', message: 'Failed to save hospital. Please try again.' });
        setSubmitError('Failed to save hospital. Please try again.');
      }
    }
  };

  const handleCancel = () => {
    setShowForm(false);
    setEditingHospital(null);
    setSubmitError('');
  };

  if (isLoading) {
    return <FormLoading message="Loading hospitals..." />;
  }

  if (error) {
    return (
      <FormStatus 
        type="error" 
        message="Failed to load hospitals. Please try again." 
      />
    );
  }

  const clinicOptions = clinics.map(clinic => ({
    value: clinic.id,
    label: clinic.name
  }));

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Hospitals</h1>
          <p className="mt-1 text-sm text-gray-600">
            Manage hospitals and medical facilities
          </p>
        </div>
        <FormButton onClick={() => setShowForm(true)}>
          <Plus className="h-4 w-4 mr-2" />
          Add Hospital
        </FormButton>
      </div>

      {/* Filters */}
      <div className="bg-white shadow rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <FormInput
            placeholder="Search hospitals..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
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
          <FormSelect
            value={clinicFilter}
            onChange={(e) => setClinicFilter(e.target.value)}
            options={[
              { value: '', label: 'All Clinics' },
              ...clinicOptions,
            ]}
          />
          <FormButton variant="outline">
            <Filter className="h-4 w-4 mr-2" />
            More Filters
          </FormButton>
        </div>
      </div>

      {/* Hospitals Grid */}
      {filteredHospitals.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <Building className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No hospitals found</h3>
            <p className="mt-1 text-sm text-gray-500">
              {searchTerm || statusFilter || clinicFilter
                ? 'Try adjusting your search criteria.' 
                : 'Get started by creating your first hospital.'
              }
            </p>
            {!searchTerm && !statusFilter && !clinicFilter && (
              <div className="mt-6">
                <FormButton onClick={() => setShowForm(true)}>
                  <Plus className="h-4 w-4 mr-2" />
                  Add First Hospital
                </FormButton>
              </div>
            )}
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {filteredHospitals.map((hospital) => (
            <HospitalCard
              key={hospital.id}
              hospital={hospital}
              onEdit={handleEdit}
              onDelete={handleDelete}
              onView={handleView}
            />
          ))}
        </div>
      )}

      {/* Hospital Form Modal */}
      <HospitalForm
        hospital={editingHospital}
        onSave={handleSave}
        onCancel={handleCancel}
        isOpen={showForm}
        isLoading={createHospitalMutation.isLoading || updateHospitalMutation.isLoading}
        submitError={submitError}
      />

      {/* Delete Confirmation Modal */}
      <ConfirmationModal
        isOpen={showDeleteConfirm}
        onClose={cancelDelete}
        onConfirm={confirmDelete}
        title="Delete Hospital"
        message={`Are you sure you want to delete "${hospitalToDelete?.name}"? This action cannot be undone.`}
        confirmText="Delete"
        cancelText="Cancel"
        variant="danger"
        isLoading={deleteHospitalMutation.isLoading}
      />
    </div>
  );
};

export default Hospitals;
