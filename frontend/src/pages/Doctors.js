import React, { useState } from 'react';
import { 
  UserCheck, 
  Plus, 
  Search, 
  Filter, 
  Edit, 
  Trash2, 
  Eye,
  Phone,
  Mail,
  Calendar,
  DollarSign,
  GraduationCap,
  Award,
  Clock
} from 'lucide-react';
import { 
  useDoctors, 
  useCreateDoctor, 
  useUpdateDoctor, 
  useDeleteDoctor, 
  useClinics, 
  useHospitals 
} from '@hooks/useApi';
import { 
  FormInput, 
  FormButton, 
  FormCard, 
  FormLoading, 
  FormStatus,
  FormSelect,
  FormTextarea 
} from '@components/forms';

const DoctorCard = ({ doctor, onEdit, onDelete, onView }) => {
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
            <UserCheck className="h-8 w-8 text-blue-600 mr-3" />
            <div>
              <h3 className="text-lg font-semibold text-gray-900">
                Dr. {doctor.first_name} {doctor.last_name}
              </h3>
              <p className="text-sm text-gray-600">{doctor.specialization}</p>
            </div>
          </div>
          
          <div className="mt-4 space-y-2">
            {doctor.email && (
              <div className="flex items-center text-sm text-gray-600">
                <Mail className="h-4 w-4 mr-2" />
                {doctor.email}
              </div>
            )}
            {doctor.phone && (
              <div className="flex items-center text-sm text-gray-600">
                <Phone className="h-4 w-4 mr-2" />
                {doctor.phone}
              </div>
            )}
            {doctor.consultation_fee && (
              <div className="flex items-center text-sm text-gray-600">
                <DollarSign className="h-4 w-4 mr-2" />
                ${doctor.consultation_fee} consultation fee
              </div>
            )}
            {doctor.experience_years && (
              <div className="flex items-center text-sm text-gray-600">
                <Award className="h-4 w-4 mr-2" />
                {doctor.experience_years} years experience
              </div>
            )}
            {doctor.education && (
              <div className="flex items-center text-sm text-gray-600">
                <GraduationCap className="h-4 w-4 mr-2" />
                {doctor.education}
              </div>
            )}
          </div>
        </div>
        
        <div className="flex items-center space-x-2 ml-4">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[doctor.status]}`}>
            {doctor.status}
          </span>
        </div>
      </div>
      
      <div className="mt-6 flex items-center justify-between">
        <div className="text-sm text-gray-500">
          License: {doctor.license_number}
        </div>
        <div className="flex items-center space-x-2">
          <button
            onClick={() => onView(doctor)}
            className="p-2 text-gray-400 hover:text-gray-600"
            title="View Details"
          >
            <Eye className="h-4 w-4" />
          </button>
          <button
            onClick={() => onEdit(doctor)}
            className="p-2 text-gray-400 hover:text-blue-600"
            title="Edit Doctor"
          >
            <Edit className="h-4 w-4" />
          </button>
          <button
            onClick={() => onDelete(doctor)}
            className="p-2 text-gray-400 hover:text-red-600"
            title="Delete Doctor"
          >
            <Trash2 className="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  );
};

const DoctorForm = ({ doctor, onSave, onCancel, isOpen, isLoading }) => {
  const { data: clinicsResponse } = useClinics();
  const { data: hospitalsResponse } = useHospitals();
  
  const clinics = clinicsResponse?.data || [];
  const hospitals = hospitalsResponse?.data || [];
  
  const [formData, setFormData] = useState({
    clinic_id: doctor?.clinic_id || '',
    hospital_id: doctor?.hospital_id || '',
    first_name: doctor?.first_name || '',
    last_name: doctor?.last_name || '',
    email: doctor?.email || '',
    phone: doctor?.phone || '',
    specialization: doctor?.specialization || '',
    license_number: doctor?.license_number || '',
    experience_years: doctor?.experience_years || '',
    education: doctor?.education || '',
    bio: doctor?.bio || '',
    consultation_fee: doctor?.consultation_fee || '',
    status: doctor?.status || 'active',
  });

  const [errors, setErrors] = useState({});

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.first_name.trim()) {
      newErrors.first_name = 'First name is required';
    }
    
    if (!formData.last_name.trim()) {
      newErrors.last_name = 'Last name is required';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required';
    }
    
    if (!formData.specialization.trim()) {
      newErrors.specialization = 'Specialization is required';
    }
    
    if (!formData.license_number.trim()) {
      newErrors.license_number = 'License number is required';
    }
    
    if (!formData.clinic_id) {
      newErrors.clinic_id = 'Clinic is required';
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

  const hospitalOptions = hospitals.map(hospital => ({
    value: hospital.id,
    label: hospital.name
  }));

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div className="mt-3">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-medium text-gray-900">
              {doctor ? 'Edit Doctor' : 'Add New Doctor'}
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
                label="First Name"
                value={formData.first_name}
                onChange={(e) => handleChange('first_name', e.target.value)}
                error={errors.first_name}
                required
              />
              
              <FormInput
                label="Last Name"
                value={formData.last_name}
                onChange={(e) => handleChange('last_name', e.target.value)}
                error={errors.last_name}
                required
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
              
              <FormSelect
                label="Clinic"
                value={formData.clinic_id}
                onChange={(e) => handleChange('clinic_id', e.target.value)}
                options={clinicOptions}
                error={errors.clinic_id}
                required
              />
              
              <FormSelect
                label="Hospital"
                value={formData.hospital_id}
                onChange={(e) => handleChange('hospital_id', e.target.value)}
                options={hospitalOptions}
                error={errors.hospital_id}
              />
              
              <FormInput
                label="Specialization"
                value={formData.specialization}
                onChange={(e) => handleChange('specialization', e.target.value)}
                error={errors.specialization}
                required
                placeholder="e.g., Cardiology, Neurology"
              />
              
              <FormInput
                label="License Number"
                value={formData.license_number}
                onChange={(e) => handleChange('license_number', e.target.value)}
                error={errors.license_number}
                required
              />
              
              <FormInput
                label="Experience (Years)"
                type="number"
                value={formData.experience_years}
                onChange={(e) => handleChange('experience_years', e.target.value)}
                error={errors.experience_years}
                min="0"
                max="50"
              />
              
              <FormInput
                label="Consultation Fee"
                type="number"
                step="0.01"
                value={formData.consultation_fee}
                onChange={(e) => handleChange('consultation_fee', e.target.value)}
                error={errors.consultation_fee}
                placeholder="0.00"
              />
              
              <FormInput
                label="Education"
                value={formData.education}
                onChange={(e) => handleChange('education', e.target.value)}
                error={errors.education}
                placeholder="Medical School, Residency"
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
              label="Bio"
              value={formData.bio}
              onChange={(e) => handleChange('bio', e.target.value)}
              error={errors.bio}
              placeholder="Brief biography and professional background"
              rows={4}
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
                {doctor ? 'Update Doctor' : 'Create Doctor'}
              </FormButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

const Doctors = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [specializationFilter, setSpecializationFilter] = useState('');
  const [clinicFilter, setClinicFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingDoctor, setEditingDoctor] = useState(null);

  const { data: doctorsResponse, isLoading, error } = useDoctors();
  const { data: clinicsResponse } = useClinics();
  const createDoctorMutation = useCreateDoctor();
  const updateDoctorMutation = useUpdateDoctor();
  const deleteDoctorMutation = useDeleteDoctor();

  const doctors = doctorsResponse?.data || [];
  const clinics = clinicsResponse?.data || [];

  const filteredDoctors = doctors.filter(doctor => {
    const matchesSearch = 
      doctor.first_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      doctor.last_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      doctor.specialization.toLowerCase().includes(searchTerm.toLowerCase()) ||
      doctor.email.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = !statusFilter || doctor.status === statusFilter;
    const matchesSpecialization = !specializationFilter || doctor.specialization === specializationFilter;
    const matchesClinic = !clinicFilter || doctor.clinic_id === parseInt(clinicFilter);
    return matchesSearch && matchesStatus && matchesSpecialization && matchesClinic;
  });

  const specializations = [...new Set(doctors.map(doctor => doctor.specialization).filter(Boolean))];

  const handleEdit = (doctor) => {
    setEditingDoctor(doctor);
    setShowForm(true);
  };

  const handleDelete = async (doctor) => {
    if (window.confirm(`Are you sure you want to delete Dr. ${doctor.first_name} ${doctor.last_name}?`)) {
      try {
        await deleteDoctorMutation.mutateAsync(doctor.id);
      } catch (error) {
        console.error('Failed to delete doctor:', error);
      }
    }
  };

  const handleView = (doctor) => {
    // TODO: Implement view details modal
    console.log('View doctor:', doctor);
  };

  const handleSave = async (formData) => {
    try {
      if (editingDoctor) {
        await updateDoctorMutation.mutateAsync({ 
          id: editingDoctor.id, 
          data: formData 
        });
      } else {
        await createDoctorMutation.mutateAsync(formData);
      }
      setShowForm(false);
      setEditingDoctor(null);
    } catch (error) {
      console.error('Failed to save doctor:', error);
    }
  };

  const handleCancel = () => {
    setShowForm(false);
    setEditingDoctor(null);
  };

  if (isLoading) {
    return <FormLoading message="Loading doctors..." />;
  }

  if (error) {
    return (
      <FormStatus 
        type="error" 
        message="Failed to load doctors. Please try again." 
      />
    );
  }

  const clinicOptions = clinics.map(clinic => ({
    value: clinic.id,
    label: clinic.name
  }));

  const specializationOptions = specializations.map(spec => ({
    value: spec,
    label: spec
  }));

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Doctors</h1>
          <p className="mt-1 text-sm text-gray-600">
            Manage your medical staff and doctors
          </p>
        </div>
        <FormButton onClick={() => setShowForm(true)}>
          <Plus className="h-4 w-4 mr-2" />
          Add Doctor
        </FormButton>
      </div>

      {/* Filters */}
      <div className="bg-white shadow rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <FormInput
            placeholder="Search doctors..."
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
            value={specializationFilter}
            onChange={(e) => setSpecializationFilter(e.target.value)}
            options={[
              { value: '', label: 'All Specializations' },
              ...specializationOptions
            ]}
          />
          <FormSelect
            value={clinicFilter}
            onChange={(e) => setClinicFilter(e.target.value)}
            options={[
              { value: '', label: 'All Clinics' },
              ...clinicOptions
            ]}
          />
        </div>
      </div>

      {/* Doctors Grid */}
      {filteredDoctors.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <UserCheck className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No doctors found</h3>
            <p className="mt-1 text-sm text-gray-500">
              {searchTerm || statusFilter || specializationFilter || clinicFilter
                ? 'Try adjusting your search criteria.' 
                : 'Get started by adding your first doctor.'
              }
            </p>
            {!searchTerm && !statusFilter && !specializationFilter && !clinicFilter && (
              <div className="mt-6">
                <FormButton onClick={() => setShowForm(true)}>
                  <Plus className="h-4 w-4 mr-2" />
                  Add First Doctor
                </FormButton>
              </div>
            )}
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {filteredDoctors.map((doctor) => (
            <DoctorCard
              key={doctor.id}
              doctor={doctor}
              onEdit={handleEdit}
              onDelete={handleDelete}
              onView={handleView}
            />
          ))}
        </div>
      )}

      {/* Doctor Form Modal */}
      <DoctorForm
        doctor={editingDoctor}
        onSave={handleSave}
        onCancel={handleCancel}
        isOpen={showForm}
        isLoading={createDoctorMutation.isLoading || updateDoctorMutation.isLoading}
      />
    </div>
  );
};

export default Doctors;
