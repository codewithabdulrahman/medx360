import React, { useState } from 'react';
import { Calendar, Users, Plus, Edit, Trash2, Eye } from 'lucide-react';
import {
  useBookings,
  useCreateBooking,
  useUpdateBooking,
  useDeleteBooking,
  useClinics,
  useDoctors,
  useServices
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

const BookingCard = ({ booking, onEdit, onDelete, onView }) => {
  return (
    <div className="bg-white shadow rounded-lg p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <div className="flex items-center">
            <Calendar className="h-8 w-8 text-blue-600 mr-3" />
            <div>
              <h3 className="text-lg font-semibold text-gray-900">{booking.patient_name}</h3>
              <p className="text-sm text-gray-600">{booking.patient_email} — {booking.patient_phone}</p>
            </div>
          </div>

          <div className="mt-4 text-sm text-gray-600">
            {booking.appointment_date} @ {booking.appointment_time} — Clinic: {booking.clinic_id || '—'}
          </div>
        </div>

        <div className="flex items-center space-x-2 ml-4">
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800`}>
            {booking.status}
          </span>
        </div>
      </div>

      <div className="mt-6 flex items-center justify-between">
        <div className="text-sm text-gray-500">Service: {booking.service_id || '—'}</div>
        <div className="flex items-center space-x-2">
          <button onClick={() => onView(booking)} className="p-2 text-gray-400 hover:text-gray-600" title="View"><Eye className="h-4 w-4" /></button>
          <button onClick={() => onEdit(booking)} className="p-2 text-gray-400 hover:text-blue-600" title="Edit"><Edit className="h-4 w-4" /></button>
          <button onClick={() => onDelete(booking)} className="p-2 text-gray-400 hover:text-red-600" title="Delete"><Trash2 className="h-4 w-4" /></button>
        </div>
      </div>
    </div>
  );
};

const BookingForm = ({ booking, onSave, onCancel, isOpen, isLoading }) => {
  const { data: clinicsResp } = useClinics();
  const { data: doctorsResp } = useDoctors();
  const { data: servicesResp } = useServices();

  const clinics = clinicsResp?.data || [];
  const doctors = doctorsResp?.data || [];
  const services = servicesResp?.data || [];

  const [formData, setFormData] = useState({
    clinic_id: booking?.clinic_id || '',
    doctor_id: booking?.doctor_id || '',
    service_id: booking?.service_id || '',
    patient_name: booking?.patient_name || '',
    patient_email: booking?.patient_email || '',
    patient_phone: booking?.patient_phone || '',
    appointment_date: booking?.appointment_date || '',
    appointment_time: booking?.appointment_time || '',
    duration_minutes: booking?.duration_minutes || 30,
    status: booking?.status || 'pending',
    notes: booking?.notes || ''
  });

  const [errors, setErrors] = useState({});

  React.useEffect(() => {
    if (booking) setFormData({
      clinic_id: booking.clinic_id || '',
      doctor_id: booking.doctor_id || '',
      service_id: booking.service_id || '',
      patient_name: booking.patient_name || '',
      patient_email: booking.patient_email || '',
      patient_phone: booking.patient_phone || '',
      appointment_date: booking.appointment_date || '',
      appointment_time: booking.appointment_time || '',
      duration_minutes: booking.duration_minutes || 30,
      status: booking.status || 'pending',
      notes: booking.notes || ''
    });
    else setFormData({ clinic_id: '', doctor_id: '', service_id: '', patient_name: '', patient_email: '', patient_phone: '', appointment_date: '', appointment_time: '', duration_minutes: 30, status: 'pending', notes: '' });
    setErrors({});
  }, [booking]);

  const validate = () => {
    const e = {};
    if (!formData.clinic_id) e.clinic_id = 'Clinic is required';
    if (!formData.patient_name || !formData.patient_name.trim()) e.patient_name = 'Patient name is required';
    if (!formData.appointment_date) e.appointment_date = 'Appointment date is required';
    if (!formData.appointment_time) e.appointment_time = 'Appointment time is required';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = (e) => { e.preventDefault(); if (validate()) onSave(formData); };
  const handleChange = (field, value) => { setFormData(prev => ({ ...prev, [field]: value })); if (errors[field]) setErrors(prev => ({ ...prev, [field]: null })); };

  const clinicOptions = clinics.map(c => ({ value: c.id, label: c.name }));
  const doctorOptions = doctors.map(d => ({ value: d.id, label: `${d.first_name} ${d.last_name}` }));
  const serviceOptions = services.map(s => ({ value: s.id, label: s.name }));

  return (
    <Modal isOpen={isOpen} onClose={onCancel} title={booking ? 'Edit Booking' : 'Create Booking'} size="md">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormSelect label="Clinic" value={formData.clinic_id} onChange={(e) => handleChange('clinic_id', e.target.value)} options={[{ value: '', label: 'Select Clinic' }, ...clinicOptions]} error={errors.clinic_id} required />
          <FormSelect label="Doctor" value={formData.doctor_id} onChange={(e) => handleChange('doctor_id', e.target.value)} options={[{ value: '', label: 'Select Doctor' }, ...doctorOptions]} />
          <FormSelect label="Service" value={formData.service_id} onChange={(e) => handleChange('service_id', e.target.value)} options={[{ value: '', label: 'Select Service' }, ...serviceOptions]} />
          <FormInput label="Patient Name" value={formData.patient_name} onChange={(e) => handleChange('patient_name', e.target.value)} error={errors.patient_name} required />
          <FormInput label="Patient Email" type="email" value={formData.patient_email} onChange={(e) => handleChange('patient_email', e.target.value)} />
          <FormInput label="Patient Phone" value={formData.patient_phone} onChange={(e) => handleChange('patient_phone', e.target.value)} />
          <FormInput label="Date" type="date" value={formData.appointment_date} onChange={(e) => handleChange('appointment_date', e.target.value)} error={errors.appointment_date} required />
          <FormInput label="Time" type="time" value={formData.appointment_time} onChange={(e) => handleChange('appointment_time', e.target.value)} error={errors.appointment_time} required />
          <FormInput label="Duration (minutes)" type="number" value={formData.duration_minutes} onChange={(e) => handleChange('duration_minutes', e.target.value)} />
          <FormSelect label="Status" value={formData.status} onChange={(e) => handleChange('status', e.target.value)} options={[{ value: 'pending', label: 'Pending' }, { value: 'confirmed', label: 'Confirmed' }, { value: 'cancelled', label: 'Cancelled' }]} />
        </div>

        <FormTextarea label="Notes" value={formData.notes} onChange={(e) => handleChange('notes', e.target.value)} />

        <div className="flex items-center justify-end space-x-3 pt-4 border-t">
          <FormButton type="button" variant="outline" onClick={onCancel}>Cancel</FormButton>
          <FormButton type="submit" loading={isLoading}>{booking ? 'Update Booking' : 'Create Booking'}</FormButton>
        </div>
      </form>
    </Modal>
  );
};

const Bookings = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [clinicFilter, setClinicFilter] = useState('');
  const [doctorFilter, setDoctorFilter] = useState('');
  const [dateFilter, setDateFilter] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [editingBooking, setEditingBooking] = useState(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [bookingToDelete, setBookingToDelete] = useState(null);

  const { data: bookingsResp, isLoading, error } = useBookings();
  const { data: clinicsResp } = useClinics();
  const { data: doctorsResp } = useDoctors();
  const createMutation = useCreateBooking();
  const updateMutation = useUpdateBooking();
  const deleteMutation = useDeleteBooking();

  const bookings = bookingsResp?.data || [];
  const clinics = clinicsResp?.data || [];
  const doctors = doctorsResp?.data || [];

  const filtered = bookings.filter(b => {
    const matchSearch = (b.patient_name?.toLowerCase() || '').includes(searchTerm.toLowerCase()) || (b.patient_email?.toLowerCase() || '').includes(searchTerm.toLowerCase());
    const matchClinic = !clinicFilter || b.clinic_id === parseInt(clinicFilter);
    const matchDoctor = !doctorFilter || b.doctor_id === parseInt(doctorFilter);
    const matchDate = !dateFilter || b.appointment_date === dateFilter;
    return matchSearch && matchClinic && matchDoctor && matchDate;
  });

  const handleEdit = (b) => { setEditingBooking(b); setShowForm(true); };
  const handleDelete = (b) => { setBookingToDelete(b); setShowDeleteConfirm(true); };
  const cancelDelete = () => { setShowDeleteConfirm(false); setBookingToDelete(null); };

  const confirmDelete = async () => {
    if (!bookingToDelete) return;
    try { await deleteMutation.mutateAsync(bookingToDelete.id); setShowDeleteConfirm(false); setBookingToDelete(null); } catch (err) { console.error('Failed to delete booking', err); }
  };

  const handleView = (b) => { alert(`${b.patient_name}\n${b.appointment_date} ${b.appointment_time}`); };

  const handleSave = async (data) => {
    try {
      if (editingBooking) {
        await updateMutation.mutateAsync({ id: editingBooking.id, data });
      } else {
        await createMutation.mutateAsync(data);
      }
      setShowForm(false); setEditingBooking(null);
    } catch (err) { console.error('Failed to save booking', err); throw err; }
  };

  const handleCancel = () => { setShowForm(false); setEditingBooking(null); };

  if (isLoading) return <FormLoading message="Loading bookings..." />;
  if (error) return <FormStatus type="error" message="Failed to load bookings. Please try again." />;

  const clinicOptions = clinics.map(c => ({ value: c.id, label: c.name }));
  const doctorOptions = doctors.map(d => ({ value: d.id, label: `${d.first_name} ${d.last_name}` }));

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Bookings</h1>
          <p className="mt-1 text-sm text-gray-600">Manage appointments and bookings</p>
        </div>
        <FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />Add Booking</FormButton>
      </div>

      <div className="bg-white shadow rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <FormInput placeholder="Search by patient..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} />
          <FormSelect value={clinicFilter} onChange={(e) => setClinicFilter(e.target.value)} options={[{ value: '', label: 'All Clinics' }, ...clinicOptions]} />
          <FormSelect value={doctorFilter} onChange={(e) => setDoctorFilter(e.target.value)} options={[{ value: '', label: 'All Doctors' }, ...doctorOptions]} />
          <FormInput type="date" value={dateFilter} onChange={(e) => setDateFilter(e.target.value)} />
        </div>
      </div>

      {filtered.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <Calendar className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No bookings found</h3>
            <p className="mt-1 text-sm text-gray-500">Get started by creating your first booking.</p>
            <div className="mt-6"><FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />Add Booking</FormButton></div>
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {filtered.map(b => (<BookingCard key={b.id} booking={b} onEdit={handleEdit} onDelete={handleDelete} onView={handleView} />))}
        </div>
      )}

      <BookingForm booking={editingBooking} onSave={handleSave} onCancel={handleCancel} isOpen={showForm} isLoading={createMutation.isLoading || updateMutation.isLoading} />

      <ConfirmationModal isOpen={showDeleteConfirm} onClose={cancelDelete} onConfirm={confirmDelete} title="Delete Booking" message={`Are you sure you want to delete booking for ${bookingToDelete?.patient_name}?`} confirmText="Delete" cancelText="Cancel" variant="danger" isLoading={deleteMutation.isLoading} />
    </div>
  );
};

export default Bookings;
