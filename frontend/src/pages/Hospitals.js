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

const HospitalForm = ({ hospital, onSave, onCancel, isOpen, isLoading }) => {
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

  const clinicOptions = clinics.map(clinic => ({
    value: clinic.id,
    label: clinic.name
  }));

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div className="mt-3">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-medium text-gray-900">
              {hospital ? 'Edit Hospital' : 'Add New Hospital'}
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
                placeholder="example.com (https:// will be added automatically)"
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
        </div>
      </div>
    </div>
  );
};

const Hospitals = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [clinicFilter, setClinicFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingHospital, setEditingHospital] = useState(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [hospitalToDelete, setHospitalToDelete] = useState(null);

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
('Success', 'Hospital deleted successfully');
      setShowDeleteConfirm(false);
      setHospitalToDelete(null);
    } catch (error) {
      console.error('Failed to delete hospital:', error);
('Error', 'Failed to delete hospital. Please try again.');
    }
  };

  const cancelDelete = () => {
    setShowDeleteConfirm(false);
    setHospitalToDelete(null);
  };

  const handleView = (hospital) => {
    // TODO: Implement view details modal
('Info', `Viewing hospital: ${hospital.name}`);
  };

  const handleSave = async (formData) => {
    try {
      if (editingHospital) {
        await updateHospitalMutation.mutateAsync({ 
          id: editingHospital.id, 
          data: formData 
        });
('Success', 'Hospital updated successfully');
      } else {
        await createHospitalMutation.mutateAsync(formData);
('Success', 'Hospital created successfully');
      }
      setShowForm(false);
      setEditingHospital(null);
    } catch (error) {
      console.error('Failed to save hospital:', error);
      
      // Show detailed validation errors if available
      if (error.message && error.message !== 'Request failed') {
('Validation Error', error.message);
      } else {
('Error', 'Failed to save hospital. Please try again.');
      }
    }
  };

  const handleCancel = () => {
    setShowForm(false);
    setEditingHospital(null);
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
