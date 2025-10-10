import React, { useState } from 'react';
import {
  Users,
  Plus,
  Edit,
  Trash2,
  Eye,
  Mail,
  Phone
} from 'lucide-react';
import {
  useStaff,
  useCreateStaffMember,
  useUpdateStaffMember,
  useDeleteStaffMember,
  useClinics,
  useHospitals
} from '@hooks/useApi';
import { useToast } from '@components/Toast';
import {
  FormInput,
  FormButton,
  FormCard,
  FormLoading,
  FormStatus,
  FormSelect
} from '@components/forms';
import Modal from '@components/Modal';
import ConfirmationModal from '@components/ConfirmationModal';

const StaffCard = ({ staff, onEdit, onDelete, onView }) => {
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
            <Users className="h-8 w-8 text-purple-600 mr-3" />
            <div>
              <h3 className="text-lg font-semibold text-gray-900">
                {staff.first_name} {staff.last_name}
              </h3>
              <p className="text-sm text-gray-600">{staff.role} {staff.department ? `— ${staff.department}` : ''}</p>
            </div>
          </div>

          <div className="mt-4 space-y-2">
            {staff.email && (
              <div className="flex items-center text-sm text-gray-600">
                <Mail className="h-4 w-4 mr-2" />
                {staff.email}
              </div>
            )}
            {staff.phone && (
              <div className="flex items-center text-sm text-gray-600">
                <Phone className="h-4 w-4 mr-2" />
                {staff.phone}
              </div>
            )}
          </div>
        </div>

        <div className="flex items-center space-x-2 ml-4">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[staff.status]}`}>
            {staff.status}
          </span>
        </div>
      </div>

      <div className="mt-6 flex items-center justify-between">
        <div className="text-sm text-gray-500">
          Clinic: {staff.clinic_id || '—'}
        </div>
        <div className="flex items-center space-x-2">
          <button onClick={() => onView(staff)} className="p-2 text-gray-400 hover:text-gray-600" title="View Details"><Eye className="h-4 w-4" /></button>
          <button onClick={() => onEdit(staff)} className="p-2 text-gray-400 hover:text-blue-600" title="Edit Staff"><Edit className="h-4 w-4" /></button>
          <button onClick={() => onDelete(staff)} className="p-2 text-gray-400 hover:text-red-600" title="Delete Staff"><Trash2 className="h-4 w-4" /></button>
        </div>
      </div>
    </div>
  );
};

const StaffForm = ({ staff, onSave, onCancel, isOpen, isLoading }) => {
  const { data: clinicsResponse } = useClinics();
  const { data: hospitalsResponse } = useHospitals();

  const clinics = clinicsResponse?.data || [];
  const hospitals = hospitalsResponse?.data || [];

  const [formData, setFormData] = useState({
    clinic_id: staff?.clinic_id || '',
    hospital_id: staff?.hospital_id || '',
    first_name: staff?.first_name || '',
    last_name: staff?.last_name || '',
    email: staff?.email || '',
    phone: staff?.phone || '',
    role: staff?.role || '',
    department: staff?.department || '',
    status: staff?.status || 'active'
  });

  const [errors, setErrors] = useState({});

  React.useEffect(() => {
    if (staff) {
      setFormData({
        clinic_id: staff.clinic_id || '',
        hospital_id: staff.hospital_id || '',
        first_name: staff.first_name || '',
        last_name: staff.last_name || '',
        email: staff.email || '',
        phone: staff.phone || '',
        role: staff.role || '',
        department: staff.department || '',
        status: staff.status || 'active'
      });
    } else {
      setFormData({
        clinic_id: '',
        hospital_id: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        role: '',
        department: '',
        status: 'active'
      });
    }
    setErrors({});
  }, [staff]);

  const validateForm = () => {
    const newErrors = {};
    if (!formData.first_name.trim()) newErrors.first_name = 'First name is required';
    if (!formData.last_name.trim()) newErrors.last_name = 'Last name is required';
    if (!formData.email.trim()) newErrors.email = 'Email is required';
    if (!formData.clinic_id) newErrors.clinic_id = 'Clinic is required';
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validateForm()) onSave(formData);
  };

  const handleChange = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    if (errors[field]) setErrors(prev => ({ ...prev, [field]: null }));
  };

  const clinicOptions = clinics.map(c => ({ value: c.id, label: c.name }));
  const hospitalOptions = hospitals.map(h => ({ value: h.id, label: h.name }));

  return (
    <Modal isOpen={isOpen} onClose={onCancel} title={staff ? 'Edit Staff' : 'Add New Staff'} size="md">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormInput label="First Name" value={formData.first_name} onChange={(e) => handleChange('first_name', e.target.value)} error={errors.first_name} required />
          <FormInput label="Last Name" value={formData.last_name} onChange={(e) => handleChange('last_name', e.target.value)} error={errors.last_name} required />
          <FormInput label="Email" type="email" value={formData.email} onChange={(e) => handleChange('email', e.target.value)} error={errors.email} required />
          <FormInput label="Phone" value={formData.phone} onChange={(e) => handleChange('phone', e.target.value)} />
          <FormSelect label="Clinic" value={formData.clinic_id} onChange={(e) => handleChange('clinic_id', e.target.value)} options={[{ value: '', label: 'Select Clinic' }, ...clinicOptions]} error={errors.clinic_id} required />
          <FormSelect label="Hospital" value={formData.hospital_id} onChange={(e) => handleChange('hospital_id', e.target.value)} options={[{ value: '', label: 'Select Hospital' }, ...hospitalOptions]} />
          <FormInput label="Role" value={formData.role} onChange={(e) => handleChange('role', e.target.value)} />
          <FormInput label="Department" value={formData.department} onChange={(e) => handleChange('department', e.target.value)} />
          <FormSelect label="Status" value={formData.status} onChange={(e) => handleChange('status', e.target.value)} options={[{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'pending', label: 'Pending' }]} />
        </div>

        <div className="flex items-center justify-end space-x-3 pt-4 border-t">
          <FormButton type="button" variant="outline" onClick={onCancel}>Cancel</FormButton>
          <FormButton type="submit" loading={isLoading}>{staff ? 'Update Staff' : 'Create Staff'}</FormButton>
        </div>
      </form>
    </Modal>
  );
};

const Staff = () => {
  const { addToast } = useToast();
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [clinicFilter, setClinicFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingStaff, setEditingStaff] = useState(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [staffToDelete, setStaffToDelete] = useState(null);

  const { data: staffResponse, isLoading, error } = useStaff();
  const { data: clinicsResponse } = useClinics();
  const createMutation = useCreateStaffMember();
  const updateMutation = useUpdateStaffMember();
  const deleteMutation = useDeleteStaffMember();

  const staff = staffResponse?.data || [];
  const clinics = clinicsResponse?.data || [];

  const filtered = staff.filter(s => {
    const matchSearch = (s.first_name?.toLowerCase() || '').includes(searchTerm.toLowerCase()) || (s.last_name?.toLowerCase() || '').includes(searchTerm.toLowerCase()) || (s.email?.toLowerCase() || '').includes(searchTerm.toLowerCase()) || (s.role?.toLowerCase() || '').includes(searchTerm.toLowerCase());
    const matchStatus = !statusFilter || s.status === statusFilter;
    const matchClinic = !clinicFilter || s.clinic_id === parseInt(clinicFilter);
    return matchSearch && matchStatus && matchClinic;
  });

  const handleEdit = (s) => { setEditingStaff(s); setShowForm(true); };
  const handleDelete = (s) => { setStaffToDelete(s); setShowDeleteConfirm(true); };
  const cancelDelete = () => { setShowDeleteConfirm(false); setStaffToDelete(null); };

  const confirmDelete = async () => {
    if (!staffToDelete) return;
    try {
      await deleteMutation.mutateAsync(staffToDelete.id);
      addToast({ type: 'success', title: 'Deleted', message: 'Staff deleted successfully' });
      setShowDeleteConfirm(false);
      setStaffToDelete(null);
    } catch (err) {
      console.error('Failed to delete staff:', err);
      addToast({ type: 'error', title: 'Error', message: 'Failed to delete staff. Please try again.' });
    }
  };

  const handleView = (s) => {
    // Simple view placeholder. Could implement a details modal.
    alert(`${s.first_name} ${s.last_name}\nRole: ${s.role}\nEmail: ${s.email}`);
  };

  const handleSave = async (data) => {
    try {
      if (editingStaff) {
        await updateMutation.mutateAsync({ id: editingStaff.id, data });
        addToast({ type: 'success', title: 'Updated', message: 'Staff updated successfully' });
      } else {
        await createMutation.mutateAsync(data);
        addToast({ type: 'success', title: 'Created', message: 'Staff created successfully' });
      }
      setShowForm(false);
      setEditingStaff(null);
    } catch (err) {
      console.error('Failed to save staff:', err);
      if (err.message && err.message !== 'Request failed') {
        addToast({ type: 'error', title: '', message: err.message });
      } else {
        addToast({ type: 'error', title: 'Error', message: 'Failed to save staff. Please try again.' });
      }
      throw err;
    }
  };

  const handleCancel = () => { setShowForm(false); setEditingStaff(null); };

  if (isLoading) return <FormLoading message="Loading staff..." />;
  if (error) return <FormStatus type="error" message="Failed to load staff. Please try again." />;

  const clinicOptions = clinics.map(c => ({ value: c.id, label: c.name }));

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Staff</h1>
          <p className="mt-1 text-sm text-gray-600">Manage non-medical staff members</p>
        </div>
        <FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />Add Staff</FormButton>
      </div>

      <div className="bg-white shadow rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <FormInput placeholder="Search staff..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} />
          <FormSelect value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} options={[{ value: '', label: 'All Statuses' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }]} />
          <FormSelect value={clinicFilter} onChange={(e) => setClinicFilter(e.target.value)} options={[{ value: '', label: 'All Clinics' }, ...clinicOptions]} />
          <div />
        </div>
      </div>

      {filtered.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <Users className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No staff found</h3>
            <p className="mt-1 text-sm text-gray-500">Get started by adding your first staff member.</p>
            <div className="mt-6">
              <FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />Add Staff</FormButton>
            </div>
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {filtered.map(s => (
            <StaffCard key={s.id} staff={s} onEdit={handleEdit} onDelete={handleDelete} onView={handleView} />
          ))}
        </div>
      )}

      <StaffForm staff={editingStaff} onSave={handleSave} onCancel={handleCancel} isOpen={showForm} isLoading={createMutation.isLoading || updateMutation.isLoading} />

      <ConfirmationModal isOpen={showDeleteConfirm} onClose={cancelDelete} onConfirm={confirmDelete} title="Delete Staff" message={`Are you sure you want to delete ${staffToDelete?.first_name} ${staffToDelete?.last_name}?`} confirmText="Delete" cancelText="Cancel" variant="danger" isLoading={deleteMutation.isLoading} />
    </div>
  );
};

export default Staff;
