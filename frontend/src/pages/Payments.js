import React, { useState } from 'react';
import { CreditCard, Plus, Trash2, CornerUpLeft } from 'lucide-react';
import {
  usePayments,
  useCreatePayment,
  useDeletePayment,
  useRefundPayment,
  useBookings
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

const PaymentCard = ({ payment, onRefund, onDelete }) => (
  <div className="bg-white shadow rounded-lg p-6 hover:shadow-md transition-shadow">
    <div className="flex items-start justify-between">
      <div className="flex items-center">
        <CreditCard className="h-8 w-8 text-indigo-600 mr-3" />
        <div>
          <h3 className="text-lg font-semibold text-gray-900">{payment.transaction_id || `#${payment.id}`}</h3>
          <p className="text-sm text-gray-600">Booking: {payment.booking_id} — {payment.payment_method} — {payment.currency} {payment.amount}</p>
        </div>
      </div>
      <div className="flex items-center space-x-2">
        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800`}>
          {payment.status}
        </span>
      </div>
    </div>

    <div className="mt-6 flex items-center justify-end space-x-2">
      {payment.status === 'completed' && <button onClick={() => onRefund(payment)} className="p-2 text-green-600 hover:text-green-800" title="Refund"><CornerUpLeft className="h-4 w-4" /></button>}
      <button onClick={() => onDelete(payment)} className="p-2 text-red-600 hover:text-red-800" title="Delete"><Trash2 className="h-4 w-4" /></button>
    </div>
  </div>
);

const PaymentForm = ({ onSave, onCancel, isOpen, isLoading, bookings }) => {
  const [formData, setFormData] = useState({ booking_id: '', amount: '', currency: 'USD', payment_method: 'card', transaction_id: '', status: 'completed' });
  const [errors, setErrors] = useState({});

  React.useEffect(() => { setErrors({}); }, [isOpen]);

  const validate = () => {
    const e = {};
    if (!formData.booking_id) e.booking_id = 'Booking is required';
    if (!formData.amount || Number.isNaN(Number(formData.amount))) e.amount = 'Amount is required';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = (ev) => { ev.preventDefault(); if (validate()) onSave(formData); };

  const bookingOptions = (bookings || []).map(b => ({ value: b.id, label: `#${b.id} — ${b.patient_name}` }));

  return (
    <Modal isOpen={isOpen} onClose={onCancel} title="Create Payment" size="md">
      <form onSubmit={handleSubmit} className="space-y-4">
        <FormSelect label="Booking" value={formData.booking_id} onChange={(e) => setFormData(prev => ({ ...prev, booking_id: e.target.value }))} options={[{ value: '', label: 'Select Booking' }, ...bookingOptions]} error={errors.booking_id} required />
        <FormInput label="Amount" type="number" value={formData.amount} onChange={(e) => setFormData(prev => ({ ...prev, amount: e.target.value }))} error={errors.amount} required />
        <FormInput label="Transaction ID" value={formData.transaction_id} onChange={(e) => setFormData(prev => ({ ...prev, transaction_id: e.target.value }))} />
        <div className="flex items-center justify-end space-x-3 pt-4 border-t">
          <FormButton type="button" variant="outline" onClick={onCancel}>Cancel</FormButton>
          <FormButton type="submit" loading={isLoading}>Create Payment</FormButton>
        </div>
      </form>
    </Modal>
  );
};

const Payments = () => {
  const { addToast } = useToast();
  const [showForm, setShowForm] = useState(false);
  const [showDelete, setShowDelete] = useState(false);
  const [toDelete, setToDelete] = useState(null);
  const [toRefund, setToRefund] = useState(null);

  const { data: paymentsResp, isLoading, error } = usePayments();
  const { data: bookingsResp } = useBookings();
  const createMutation = useCreatePayment();
  const deleteMutation = useDeletePayment();
  const refundMutation = useRefundPayment();

  const payments = paymentsResp?.data || [];
  const bookings = bookingsResp?.data || [];

  const handleDelete = (p) => { setToDelete(p); setShowDelete(true); };
  const cancelDelete = () => { setShowDelete(false); setToDelete(null); };

  const confirmDelete = async () => {
    if (!toDelete) return;
    try {
      await deleteMutation.mutateAsync(toDelete.id);
      addToast({ type: 'success', title: 'Deleted', message: 'Payment deleted successfully' });
      setShowDelete(false);
      setToDelete(null);
    } catch (err) {
      console.error(err);
      addToast({ type: 'error', title: 'Error', message: 'Failed to delete payment. Please try again.' });
    }
  };

  const handleRefund = async (p) => {
    setToRefund(p);
    if (!confirm(`Refund payment ${p.id}?`)) return;
    try {
      await refundMutation.mutateAsync(p.id);
      addToast({ type: 'success', title: 'Refunded', message: 'Payment refunded successfully' });
    } catch (err) {
      console.error(err);
      addToast({ type: 'error', title: 'Error', message: 'Failed to refund payment. Please try again.' });
    }
  };

  const handleSave = async (data) => { try { await createMutation.mutateAsync(data); addToast({ type: 'success', title: 'Created', message: 'Payment created successfully' }); setShowForm(false); } catch (err) { console.error(err); addToast({ type: 'error', title: 'Error', message: 'Failed to create payment. Please try again.' }); } };

  if (isLoading) return <FormLoading message="Loading payments..." />;
  if (error) return <FormStatus type="error" message="Failed to load payments" />;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Payments</h1>
          <p className="mt-1 text-sm text-gray-600">Manage payments and refunds</p>
        </div>
        <FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />Create Payment</FormButton>
      </div>

      {payments.length === 0 ? (
        <FormCard>
          <div className="text-center py-12">
            <CreditCard className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No payments found</h3>
            <p className="mt-1 text-sm text-gray-500">Create a payment to begin tracking transactions.</p>
            <div className="mt-6"><FormButton onClick={() => setShowForm(true)}><Plus className="h-4 w-4 mr-2" />Create Payment</FormButton></div>
          </div>
        </FormCard>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {payments.map(p => (<PaymentCard key={p.id} payment={p} onRefund={handleRefund} onDelete={handleDelete} />))}
        </div>
      )}

      <PaymentForm onSave={handleSave} onCancel={() => setShowForm(false)} isOpen={showForm} isLoading={createMutation.isLoading} bookings={bookings} />

      <ConfirmationModal isOpen={showDelete} onClose={cancelDelete} onConfirm={confirmDelete} title="Delete Payment" message={`Are you sure you want to delete payment #${toDelete?.id}?`} confirmText="Delete" cancelText="Cancel" variant="danger" isLoading={deleteMutation.isLoading} />
    </div>
  );
};

export default Payments;
