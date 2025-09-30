import React, { useState } from 'react';
import { 
  Stethoscope, 
  Plus, 
  Search, 
  Filter, 
  Edit, 
  Trash2, 
  Eye,
  Clock,
  DollarSign,
  Tag,
  Building2,
  Building
} from 'lucide-react';
import { 
  useServices, 
  useCreateService, 
  useUpdateService, 
  useDeleteService, 
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
import Modal from '@components/Modal';
import ConfirmationModal from '@components/ConfirmationModal';
import { useToastContext } from '@components/ToastContext';

const ServiceCard = ({ service, onEdit, onDelete, onView }) => {
  const statusColors = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
  };

  return (
    <div className="bg-white shadow rounded-lg p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <div className="flex items-center">
            <Stethoscope className="h-8 w-8 text-green-600 mr-3" />
            <div>
              <h3 className="text-lg font-semibold text-gray-900">{service.name}</h3>
              <p className="text-sm text-gray-600">{service.description}</p>
            </div>
          </div>
          
          <div className="mt-4 space-y-2">
            {service.category && (
              <div className="flex items-center text-sm text-gray-600">
                <Tag className="h-4 w-4 mr-2" />
                {service.category}
              </div>
            )}
            {service.duration_minutes && (
              <div className="flex items-center text-sm text-gray-600">
                <Clock className="h-4 w-4 mr-2" />
                {service.duration_minutes} minutes
              </div>
            )}
            {service.price && (
              <div className="flex items-center text-sm text-gray-600">
                <DollarSign className="h-4 w-4 mr-2" />
                ${service.price}
              </div>
            )}
          </div>
        </div>
        
        <div className="flex items-center space-x-2 ml-4">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[service.status]}`}>
            {service.status}
          </span>
        </div>
      </div>
      
      <div className="mt-6 flex items-center justify-between">
        <div className="text-sm text-gray-500">
          Created {new Date(service.created_at).toLocaleDateString()}
        </div>
        <div className="flex items-center space-x-2">
          <button
            onClick={() => onView(service)}
            className="p-2 text-gray-400 hover:text-gray-600"
            title="View Details"
          >
            <Eye className="h-4 w-4" />
          </button>
          <button
            onClick={() => onEdit(service)}
            className="p-2 text-gray-400 hover:text-blue-600"
            title="Edit Service"
          >
            <Edit className="h-4 w-4" />
          </button>
          <button
            onClick={() => onDelete(service)}
            className="p-2 text-gray-400 hover:text-red-600"
            title="Delete Service"
          >
            <Trash2 className="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  );
};

const ServiceForm = ({ service, onSave, onCancel, isOpen, isLoading }) => {
  const { data: clinicsResponse } = useClinics();
  const { data: hospitalsResponse } = useHospitals();
  
  const clinics = clinicsResponse?.data || [];
  const hospitals = hospitalsResponse?.data || [];
  
  const [formData, setFormData] = useState({
    clinic_id: service?.clinic_id || '',
    hospital_id: service?.hospital_id || '',
    name: service?.name || '',
    description: service?.description || '',
    duration_minutes: service?.duration_minutes || '',
    price: service?.price || '',
    category: service?.category || '',
    status: service?.status || 'active',
  });

  const [errors, setErrors] = useState({});

  // Reset form when service changes
  React.useEffect(() => {
    if (service) {
      setFormData({
        clinic_id: service.clinic_id || '',
        hospital_id: service.hospital_id || '',
        name: service.name || '',
        description: service.description || '',
        duration_minutes: service.duration_minutes || '',
        price: service.price || '',
        category: service.category || '',
        status: service.status || 'active',
      });
    } else {
      setFormData({
        clinic_id: '',
        hospital_id: '',
        name: '',
        description: '',
        duration_minutes: '',
        price: '',
        category: '',
        status: 'active',
      });
    }
    setErrors({});
  }, [service]);

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.name.trim()) {
      newErrors.name = 'Service name is required';
    }
    
    if (!formData.clinic_id) {
      newErrors.clinic_id = 'Clinic is required';
    }
    
    if (!formData.duration_minutes) {
      newErrors.duration_minutes = 'Duration is required';
    } else if (parseInt(formData.duration_minutes) <= 0) {
      newErrors.duration_minutes = 'Duration must be greater than 0';
    }
    
    if (!formData.price) {
      newErrors.price = 'Price is required';
    } else if (parseFloat(formData.price) < 0) {
      newErrors.price = 'Price cannot be negative';
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

  const hospitalOptions = hospitals.map(hospital => ({
    value: hospital.id,
    label: hospital.name
  }));

  const categoryOptions = [
    { value: 'Consultation', label: 'Consultation' },
    { value: 'Diagnostic', label: 'Diagnostic' },
    { value: 'Treatment', label: 'Treatment' },
    { value: 'Surgery', label: 'Surgery' },
    { value: 'Therapy', label: 'Therapy' },
    { value: 'Emergency', label: 'Emergency' },
    { value: 'Preventive', label: 'Preventive' },
    { value: 'Other', label: 'Other' },
  ];

  return (
    <Modal
      isOpen={isOpen}
      onClose={onCancel}
      title={service ? 'Edit Service' : 'Add New Service'}
      size="lg"
    >
      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormInput
            label="Service Name"
            value={formData.name}
            onChange={(e) => handleChange('name', e.target.value)}
            error={errors.name}
            required
            placeholder="e.g., General Consultation"
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
          
          <FormSelect
            label="Category"
            value={formData.category}
            onChange={(e) => handleChange('category', e.target.value)}
            options={categoryOptions}
            error={errors.category}
          />
          
          <FormInput
            label="Duration (Minutes)"
            type="number"
            value={formData.duration_minutes}
            onChange={(e) => handleChange('duration_minutes', e.target.value)}
            error={errors.duration_minutes}
            required
            min="1"
            max="480"
          />
          
          <FormInput
            label="Price"
            type="number"
            step="0.01"
            value={formData.price}
            onChange={(e) => handleChange('price', e.target.value)}
            error={errors.price}
            required
            min="0"
            placeholder="0.00"
          />
          
          <FormSelect
            label="Status"
            value={formData.status}
            onChange={(e) => handleChange('status', e.target.value)}
            options={[
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
            ]}
            error={errors.status}
          />
        </div>
        
        <FormTextarea
          label="Description"
          value={formData.description}
          onChange={(e) => handleChange('description', e.target.value)}
          error={errors.description}
          placeholder="Brief description of the service"
          rows={3}
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
            {service ? 'Update Service' : 'Create Service'}
          </FormButton>
        </div>
      </form>
    </Modal>
  );
};

const Services = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('');
  const [clinicFilter, setClinicFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingService, setEditingService] = useState(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [serviceToDelete, setServiceToDelete] = useState(null);

  const { data: servicesResponse, isLoading, error } = useServices();
  const { data: clinicsResponse } = useClinics();
  const createServiceMutation = useCreateService();
  const updateServiceMutation = useUpdateService();
  const deleteServiceMutation = useDeleteService();
  const toast = useToastContext();

  const services = servicesResponse?.data || [];
  const clinics = clinicsResponse?.data || [];

  const filteredServices = services.filter(service => {
    const matchesSearch = 
      (service.name?.toLowerCase() || '').includes(searchTerm.toLowerCase()) ||
      (service.description?.toLowerCase() || '').includes(searchTerm.toLowerCase()) ||
      (service.category?.toLowerCase() || '').includes(searchTerm.toLowerCase());
    const matchesStatus = !statusFilter || service.status === statusFilter;
    const matchesCategory = !categoryFilter || service.category === categoryFilter;
    const matchesClinic = !clinicFilter || service.clinic_id === parseInt(clinicFilter);
    return matchesSearch && matchesStatus && matchesCategory && matchesClinic;
  });

  const categories = [...new Set(services.map(service => service.category).filter(Boolean))];

  const handleEdit = (service) => {
    setEditingService(service);
    setShowForm(true);
  };

  const handleDelete = (service) => {
    setServiceToDelete(service);
    setShowDeleteConfirm(true);
  };

  const confirmDelete = async () => {
    if (!serviceToDelete) return;
    
    try {
      await deleteServiceMutation.mutateAsync(serviceToDelete.id);
      toast.success('Success', 'Service deleted successfully');
      setShowDeleteConfirm(false);
      setServiceToDelete(null);
    } catch (error) {
      console.error('Failed to delete service:', error);
      toast.error('Error', 'Failed to delete service. Please try again.');
    }
  };

  const cancelDelete = () => {
    setShowDeleteConfirm(false);
    setServiceToDelete(null);
  };

  const handleView = (service) => {
    // TODO: Implement view details modal
    toast.info('Info', `Viewing service: ${service.name}`);
  };

  const handleSave = async (formData) => {
    try {
      if (editingService) {
        await updateServiceMutation.mutateAsync({ 
          id: editingService.id, 
          data: formData 
        });
        toast.success('Success', 'Service updated successfully');
      } else {
        await createServiceMutation.mutateAsync(formData);
        toast.success('Success', 'Service created successfully');
      }
      setShowForm(false);
      setEditingService(null);
    } catch (error) {
      console.error('Failed to save service:', error);
      
      // Show detailed validation errors if available
      if (error.message && error.message !== 'Request failed') {
        toast.error('Validation Error', error.message);
      } else {
        toast.error('Error', 'Failed to save service. Please try again.');
      }
    }
  };

  const handleCancel = () => {
    setShowForm(false);
    setEditingService(null);
  };

  if (isLoading) {
    return <FormLoading message="Loading services..." />;
  }

  if (error) {
    return (
      <FormStatus 
        type="error" 
        message="Failed to load services. Please try again." 
      />
    );
  }

  const clinicOptions = clinics.map(clinic => ({
    value: clinic.id,
    label: clinic.name
  }));

  const categoryOptions = categories.map(category => ({
    value: category,
    label: category
  }));

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Services</h1>
          <p className="mt-1 text-sm text-gray-600">
            Manage your medical services and treatments
          </p>
        </div>
        <FormButton onClick={() => setShowForm(true)}>
          <Plus className="h-4 w-4 mr-2" />
          Add Service
        </FormButton>
      </div>

      {/* Filters */}
      <div className="bg-white shadow rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <FormInput
            placeholder="Search services..."
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
            ]}
          />
          <FormSelect
            value={categoryFilter}
            onChange={(e) => setCategoryFilter(e.target.value)}
            options={[
              { value: '', label: 'All Categories' },
              ...categoryOptions
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

      {/* Services Grid */}
      {filteredServices.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <Stethoscope className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No services found</h3>
            <p className="mt-1 text-sm text-gray-500">
              {searchTerm || statusFilter || categoryFilter || clinicFilter
                ? 'Try adjusting your search criteria.' 
                : 'Get started by adding your first service.'
              }
            </p>
            {!searchTerm && !statusFilter && !categoryFilter && !clinicFilter && (
              <div className="mt-6">
                <FormButton onClick={() => setShowForm(true)}>
                  <Plus className="h-4 w-4 mr-2" />
                  Add First Service
                </FormButton>
              </div>
            )}
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {filteredServices.map((service) => (
            <ServiceCard
              key={service.id}
              service={service}
              onEdit={handleEdit}
              onDelete={handleDelete}
              onView={handleView}
            />
          ))}
        </div>
      )}

      {/* Service Form Modal */}
      <ServiceForm
        service={editingService}
        onSave={handleSave}
        onCancel={handleCancel}
        isOpen={showForm}
        isLoading={createServiceMutation.isLoading || updateServiceMutation.isLoading}
      />

      {/* Delete Confirmation Modal */}
      <ConfirmationModal
        isOpen={showDeleteConfirm}
        onClose={cancelDelete}
        onConfirm={confirmDelete}
        title="Delete Service"
        message={`Are you sure you want to delete "${serviceToDelete?.name}"? This action cannot be undone.`}
        confirmText="Delete"
        cancelText="Cancel"
        variant="danger"
        isLoading={deleteServiceMutation.isLoading}
      />
    </div>
  );
};

export default Services;
