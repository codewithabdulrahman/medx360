import React, { useState } from 'react';
import { Stethoscope, Plus, Edit, Trash2, Check } from 'lucide-react';
import {
  useConsultations,
  useCreateConsultation,
  useUpdateConsultation,
  useDeleteConsultation,
  useCompleteConsultation,
  useBookings,
  useDoctors
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

const ConsultationCard = ({ consultation, onEdit, onDelete, onComplete }) => {
  return (
    <div className="bg-white shadow rounded-lg p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <div className="flex items-center">
            <Stethoscope className="h-8 w-8 text-green-600 mr-3" />
            <div>
              <h3 className="text-lg font-semibold text-gray-900">{consultation.diagnosis || 'Consultation'}</h3>
              <p className="text-sm text-gray-600">Booking: {consultation.booking_id} — Doctor: {consultation.doctor_id}</p>
            </div>
          </div>

          <div className="mt-4 text-sm text-gray-600">{consultation.notes}</div>
        </div>

        <div className="flex items-center space-x-2 ml-4">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800`}>
            {consultation.status}
          </span>
        </div>
      </div>

      <div className="mt-6 flex items-center justify-between">
        <div className="text-sm text-gray-500">Follow up: {consultation.follow_up_date || '—'}</div>
        <div className="flex items-center space-x-2">
          <button onClick={() => onComplete(consultation)} className="p-2 text-green-500 hover:text-green-700" title="Complete"><Check className="h-4 w-4" /></button>
          <button onClick={() => onEdit(consultation)} className="p-2 text-gray-400 hover:text-blue-600" title="Edit"><Edit className="h-4 w-4" /></button>
          <button onClick={() => onDelete(consultation)} className="p-2 text-gray-400 hover:text-red-600" title="Delete"><Trash2 className="h-4 w-4" /></button>
        </div>
      </div>
    </div>
  );
};

const ConsultationForm = ({ consultation, onSave, onCancel, isOpen, isLoading }) => {
  const { data: bookingsResp } = useBookings();
  const { data: doctorsResp } = useDoctors();

  const bookings = bookingsResp?.data || [];
  const doctors = doctorsResp?.data || [];

  const [formData, setFormData] = useState({
    booking_id: consultation?.booking_id || '',
    doctor_id: consultation?.doctor_id || '',
    patient_id: consultation?.patient_id || '',
    consultation_type: consultation?.consultation_type || 'in_person',
    diagnosis: consultation?.diagnosis || '',
    prescription: consultation?.prescription || '',
    notes: consultation?.notes || '',
    follow_up_date: consultation?.follow_up_date || '',
    status: consultation?.status || 'scheduled'
  });

  const [errors, setErrors] = useState({});

  React.useEffect(() => {
    if (consultation) setFormData({
      booking_id: consultation.booking_id || '',
      doctor_id: consultation.doctor_id || '',
      patient_id: consultation.patient_id || '',
      consultation_type: consultation.consultation_type || 'in_person',
      diagnosis: consultation.diagnosis || '',
      prescription: consultation.prescription || '',
      notes: consultation.notes || '',
      follow_up_date: consultation.follow_up_date || '',
      status: consultation.status || 'scheduled'
    });
    else setFormData({ booking_id: '', doctor_id: '', patient_id: '', consultation_type: 'in_person', diagnosis: '', prescription: '', notes: '', follow_up_date: '', status: 'scheduled' });
    setErrors({});
  }, [consultation]);

  const validate = () => {
    const e = {};
    if (!formData.booking_id) e.booking_id = 'Booking is required';
    if (!formData.doctor_id) e.doctor_id = 'Doctor is required';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = (e) => { e.preventDefault(); if (validate()) onSave(formData); };
  const handleChange = (field, value) => { setFormData(prev => ({ ...prev, [field]: value })); if (errors[field]) setErrors(prev => ({ ...prev, [field]: null })); };

  const bookingOptions = bookings.map(b => ({ value: b.id, label: `#${b.id} — ${b.patient_name}` }));
  const doctorOptions = doctors.map(d => ({ value: d.id, label: `${d.first_name} ${d.last_name}` }));

  return (
    <Modal isOpen={isOpen} onClose={onCancel} title={consultation ? 'Edit Consultation' : 'New Consultation'} size="md">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormSelect label="Booking" value={formData.booking_id} onChange={(e) => handleChange('booking_id', e.target.value)} options={[{ value: '', label: 'Select Booking' }, ...bookingOptions]} error={errors.booking_id} required />
          <FormSelect label="Doctor" value={formData.doctor_id} onChange={(e) => handleChange('doctor_id', e.target.value)} options={[{ value: '', label: 'Select Doctor' }, ...doctorOptions]} error={errors.doctor_id} required />
          <FormSelect label="Type" value={formData.consultation_type} onChange={(e) => handleChange('consultation_type', e.target.value)} options={[{ value: 'in_person', label: 'In Person' }, { value: 'video', label: 'Video' }, { value: 'phone', label: 'Phone' }]} />
          <FormSelect label="Status" value={formData.status} onChange={(e) => handleChange('status', e.target.value)} options={[{ value: 'scheduled', label: 'Scheduled' }, { value: 'in_progress', label: 'In Progress' }, { value: 'completed', label: 'Completed' }, { value: 'cancelled', label: 'Cancelled' }]} />
        </div>

        <FormTextarea label="Diagnosis" value={formData.diagnosis} onChange={(e) => handleChange('diagnosis', e.target.value)} />
        <FormTextarea label="Prescription" value={formData.prescription} onChange={(e) => handleChange('prescription', e.target.value)} />
        <FormTextarea label="Notes" value={formData.notes} onChange={(e) => handleChange('notes', e.target.value)} />

        <div className="flex items-center justify-end space-x-3 pt-4 border-t">
          <FormButton type="button" variant="outline" onClick={onCancel}>Cancel</FormButton>
          <FormButton type="submit" loading={isLoading}>{consultation ? 'Update' : 'Create'}</FormButton>
        </div>
      </form>
    </Modal>
  );
};

const Consultations = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [doctorFilter, setDoctorFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editing, setEditing] = useState(null);
  const [showDelete, setShowDelete] = useState(false);
  const [toDelete, setToDelete] = useState(null);

  const { data: consultResp, isLoading, error } = useConsultations();
  const { data: doctorsResp } = useDoctors();
  const { data: bookingsResp } = useBookings();
  const createMutation = useCreateConsultation();
  const updateMutation = useUpdateConsultation();
  const deleteMutation = useDeleteConsultation();
  const completeMutation = useCompleteConsultation();

  const consultations = consultResp?.data || [];
  const doctors = doctorsResp?.data || [];
  const bookings = bookingsResp?.data || [];

  const filtered = consultations.filter(c => {
    const matchSearch = (c.diagnosis || '').toLowerCase().includes(searchTerm.toLowerCase()) || (c.notes || '').toLowerCase().includes(searchTerm.toLowerCase());
    const matchDoctor = !doctorFilter || c.doctor_id === parseInt(doctorFilter);
    return matchSearch && matchDoctor;
  });

  const handleEdit = (c) => { setEditing(c); setShowForm(true); };
  const handleDelete = (c) => { setToDelete(c); setShowDelete(true); };
  const cancelDelete = () => { setShowDelete(false); setToDelete(null); };

  const confirmDelete = async () => {
    if (!toDelete) return;
    try { await deleteMutation.mutateAsync(toDelete.id); setShowDelete(false); setToDelete(null); } catch (err) { console.error(err); }
  };

  const handleComplete = async (c) => {
    try { await completeMutation.mutateAsync(c.id); } catch (err) { console.error(err); }
  };

  const handleSave = async (data) => {
    try {
      if (editing) {
        await updateMutation.mutateAsync({ id: editing.id, data });
      } else {
        await createMutation.mutateAsync(data);
      }
      setShowForm(false); setEditing(null);
    } catch (err) { console.error(err); }
  };

  if (isLoading) return <FormLoading message="Loading consultations..." />;
  if (error) return <FormStatus type="error" message="Failed to load consultations" />;

  const doctorOptions = doctors.map(d => ({ value: d.id, label: `${d.first_name} ${d.last_name}` }));

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Consultations</h1>
          <p className="mt-1 text-sm text-gray-600">Manage patient consultations and records</p>
        </div>
        <FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />New Consultation</FormButton>
      </div>

      <div className="bg-white shadow rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormInput placeholder="Search..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} />
          <FormSelect value={doctorFilter} onChange={(e) => setDoctorFilter(e.target.value)} options={[{ value: '', label: 'All Doctors' }, ...doctorOptions]} />
        </div>
      </div>

      {filtered.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <Stethoscope className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No consultations found</h3>
            <p className="mt-1 text-sm text-gray-500">Create your first consultation or select a booking to view records.</p>
            <div className="mt-6"><FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />New Consultation</FormButton></div>
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {filtered.map(c => (<ConsultationCard key={c.id} consultation={c} onEdit={handleEdit} onDelete={handleDelete} onComplete={handleComplete} />))}
        </div>
      )}

      <ConsultationForm consultation={editing} onSave={handleSave} onCancel={() => { setShowForm(false); setEditing(null); }} isOpen={showForm} isLoading={createMutation.isLoading || updateMutation.isLoading} />

      <ConfirmationModal isOpen={showDelete} onClose={cancelDelete} onConfirm={confirmDelete} title="Delete Consultation" message={`Are you sure you want to delete this consultation?`} confirmText="Delete" cancelText="Cancel" variant="danger" isLoading={deleteMutation.isLoading} />
    </div>
  );
};

export default Consultations;
