import React, { useEffect, useState } from 'react';
import { FormCard, FormGrid, FormInput, FormCheckbox, FormButton, FormStatus, FormSection } from '@components/forms';
import { useSettings, useSaveSettings } from '../hooks/useApi';

const Settings = () => {
  const { data: settings, isLoading, isError } = useSettings();
  const saveMutation = useSaveSettings();

  const [form, setForm] = useState({
    booking_advance_days: 30,
    booking_cancellation_hours: 24,
    email_notifications: 1,
    sms_notifications: 0,
    reminder_notifications: 1,
    timezone: '',
    date_format: 'Y-m-d',
    time_format: 'H:i',
    currency: 'USD',
    currency_symbol: '$',
    payment_gateway: 'stripe',
    booking_confirmation: 1
  });

  useEffect(() => {
    if (settings) {
      setForm((prev) => ({ ...prev, ...settings }));
    }
  }, [settings]);

  const handleChange = (key) => (e) => {
    const val = e.target.type === 'checkbox' ? (e.target.checked ? 1 : 0) : e.target.value;
    setForm((s) => ({ ...s, [key]: val }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await saveMutation.mutateAsync(form);
    } catch (err) {
      // handled via mutation error state
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Settings</h1>
        <p className="mt-1 text-sm text-gray-600">Configure system-wide settings for bookings, notifications, and payments.</p>
      </div>

      <FormCard>
        {isLoading && <div className="py-8">Loading...</div>}
        {isError && <FormStatus type="error" message="Failed to load settings" />}

        {!isLoading && (
          <form onSubmit={handleSubmit} className="space-y-6">
            <FormSection title="Booking Settings">
              <FormGrid cols={2}>
                <FormInput label="Booking advance days" type="number" value={form.booking_advance_days} onChange={handleChange('booking_advance_days')} />
                <FormInput label="Cancellation hours" type="number" value={form.booking_cancellation_hours} onChange={handleChange('booking_cancellation_hours')} />
                <FormInput label="Default timezone" value={form.timezone || ''} onChange={handleChange('timezone')} />
                <FormInput label="Date format" value={form.date_format || ''} onChange={handleChange('date_format')} />
                <FormInput label="Time format" value={form.time_format || ''} onChange={handleChange('time_format')} />
              </FormGrid>
            </FormSection>

            <FormSection title="Notifications & Confirmation">
              <FormGrid cols={2}>
                <div>
                  <FormCheckbox label="Email notifications" checked={!!form.email_notifications} onChange={handleChange('email_notifications')} />
                </div>
                <div>
                  <FormCheckbox label="SMS notifications" checked={!!form.sms_notifications} onChange={handleChange('sms_notifications')} />
                </div>
                <div>
                  <FormCheckbox label="Reminder notifications" checked={!!form.reminder_notifications} onChange={handleChange('reminder_notifications')} />
                </div>
                <div>
                  <FormCheckbox label="Require booking confirmation" checked={!!form.booking_confirmation} onChange={handleChange('booking_confirmation')} />
                </div>
              </FormGrid>
            </FormSection>

            <FormSection title="Payments">
              <FormGrid cols={2}>
                <FormInput label="Currency" value={form.currency || ''} onChange={handleChange('currency')} />
                <FormInput label="Currency symbol" value={form.currency_symbol || ''} onChange={handleChange('currency_symbol')} />
                <FormInput label="Payment gateway" value={form.payment_gateway || ''} onChange={handleChange('payment_gateway')} />
              </FormGrid>
            </FormSection>

            <div className="flex items-center space-x-3">
              <FormButton type="submit" loading={saveMutation.isLoading}>Save settings</FormButton>
              {saveMutation.isError && <FormStatus type="error" message={saveMutation.error?.message || 'Failed to save settings'} />}
              {saveMutation.isSuccess && <FormStatus type="success" message="Settings saved successfully" />}
            </div>
          </form>
        )}
      </FormCard>
    </div>
  );
};

export default Settings;
