
import React, { useState, useEffect } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Spinner, Icons, Button } from '../components/Shared';

type SettingsTab = 'general' | 'payments' | 'notifications';

export const Settings = () => {
  const { data, actions, isLoading } = useAdmin();
  const [formData, setFormData] = useState<any>(null);
  const [activeTab, setActiveTab] = useState<SettingsTab>('general');
  const [isSaving, setIsSaving] = useState(false);
  const [hasChanges, setHasChanges] = useState(false);

  useEffect(() => {
    if (data.settings) {
      setFormData({
        site_name: data.settings.site_name || 'EduTrack LMS',
        currency: data.settings.currency || 'ZMW',
        registration_fee: data.settings.registration_fee || 0,
        allow_partial_payments: data.settings.allow_partial_payments || false,
        email_notifications: data.settings.email_notifications !== false,
        sms_notifications: data.settings.sms_notifications || false,
        auto_enroll: data.settings.auto_enroll || false,
        require_approval: data.settings.require_approval || false,
      });
    }
  }, [data.settings]);

  if (isLoading || !formData) return <Spinner />;

  const handleChange = (field: string, value: any) => {
    setFormData((prev: any) => ({ ...prev, [field]: value }));
    setHasChanges(true);
  };

  const handleSave = async () => {
    setIsSaving(true);
    await actions.updateSettings(formData);
    setIsSaving(false);
    setHasChanges(false);
  };

  const tabs = [
    { id: 'general', label: 'General', icon: Icons.Settings },
    { id: 'payments', label: 'Payments', icon: Icons.Cash },
    { id: 'notifications', label: 'Notifications', icon: Icons.Bell },
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-800">System Settings</h2>
          <p className="text-sm text-gray-500 mt-1">Configure your LMS platform settings</p>
        </div>
        <Button
          onClick={handleSave}
          disabled={!hasChanges}
          loading={isSaving}
        >
          <Icons.Check />
          <span className="ml-2">Save Changes</span>
        </Button>
      </div>

      <div className="flex flex-col lg:flex-row gap-6">
        {/* Sidebar Navigation */}
        <div className="w-full lg:w-64 flex-shrink-0">
          <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <nav className="p-2">
              {tabs.map(tab => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id as SettingsTab)}
                  className={`w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all mb-1 ${
                    activeTab === tab.id
                      ? 'bg-primary text-white'
                      : 'text-gray-600 hover:bg-gray-100'
                  }`}
                >
                  <tab.icon />
                  <span className="ml-3">{tab.label}</span>
                </button>
              ))}
            </nav>
          </div>
        </div>

        {/* Settings Content */}
        <div className="flex-1">
          <div className="bg-white rounded-xl shadow-sm border border-gray-100">
            {/* General Settings */}
            {activeTab === 'general' && (
              <div className="p-6 space-y-6">
                <div>
                  <h3 className="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                  <div className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Site Name
                      </label>
                      <input
                        type="text"
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        value={formData.site_name}
                        onChange={e => handleChange('site_name', e.target.value)}
                        placeholder="Enter your site name"
                      />
                      <p className="mt-1 text-xs text-gray-500">
                        This name will appear in emails and on the platform.
                      </p>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Currency
                      </label>
                      <select
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white"
                        value={formData.currency}
                        onChange={e => handleChange('currency', e.target.value)}
                      >
                        <option value="ZMW">ZMW - Zambian Kwacha</option>
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="ZAR">ZAR - South African Rand</option>
                        <option value="NGN">NGN - Nigerian Naira</option>
                        <option value="KES">KES - Kenyan Shilling</option>
                      </select>
                    </div>

                    <div className="pt-4 border-t border-gray-100">
                      <h4 className="text-sm font-medium text-gray-700 mb-3">Course Settings</h4>
                      <div className="space-y-3">
                        <label className="flex items-center">
                          <input
                            type="checkbox"
                            checked={formData.auto_enroll}
                            onChange={e => handleChange('auto_enroll', e.target.checked)}
                            className="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                          />
                          <span className="ml-3 text-sm text-gray-700">
                            Auto-enroll students after payment
                          </span>
                        </label>
                        <label className="flex items-center">
                          <input
                            type="checkbox"
                            checked={formData.require_approval}
                            onChange={e => handleChange('require_approval', e.target.checked)}
                            className="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                          />
                          <span className="ml-3 text-sm text-gray-700">
                            Require admin approval for new courses
                          </span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Payment Settings */}
            {activeTab === 'payments' && (
              <div className="p-6 space-y-6">
                <div>
                  <h3 className="text-lg font-medium text-gray-900 mb-4">Payment Settings</h3>
                  <div className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Registration Fee ({formData.currency})
                      </label>
                      <div className="relative">
                        <span className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                          {formData.currency}
                        </span>
                        <input
                          type="number"
                          min="0"
                          step="0.01"
                          className="w-full pl-16 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                          value={formData.registration_fee}
                          onChange={e => handleChange('registration_fee', parseFloat(e.target.value) || 0)}
                        />
                      </div>
                      <p className="mt-1 text-xs text-gray-500">
                        One-time registration fee for new students (set to 0 to disable).
                      </p>
                    </div>

                    <div className="pt-4 border-t border-gray-100">
                      <h4 className="text-sm font-medium text-gray-700 mb-3">Payment Options</h4>
                      <div className="space-y-3">
                        <label className="flex items-center">
                          <input
                            type="checkbox"
                            checked={formData.allow_partial_payments}
                            onChange={e => handleChange('allow_partial_payments', e.target.checked)}
                            className="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                          />
                          <span className="ml-3 text-sm text-gray-700">
                            Allow partial payments
                          </span>
                        </label>
                        <p className="ml-7 text-xs text-gray-500">
                          Students can pay in installments for courses.
                        </p>
                      </div>
                    </div>

                    <div className="pt-4 border-t border-gray-100">
                      <div className="bg-blue-50 border border-blue-100 rounded-lg p-4">
                        <div className="flex items-start">
                          <Icons.InfoCircle />
                          <div className="ml-3">
                            <h4 className="text-sm font-medium text-blue-800">Payment Gateway</h4>
                            <p className="text-xs text-blue-600 mt-1">
                              Payment gateway integration is configured via environment variables.
                              Contact your system administrator to modify payment provider settings.
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Notification Settings */}
            {activeTab === 'notifications' && (
              <div className="p-6 space-y-6">
                <div>
                  <h3 className="text-lg font-medium text-gray-900 mb-4">Notification Settings</h3>
                  <div className="space-y-4">
                    <div className="space-y-3">
                      <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                          <span className="text-sm font-medium text-gray-700">Email Notifications</span>
                          <p className="text-xs text-gray-500 mt-1">
                            Send email notifications for enrollments, payments, and announcements
                          </p>
                        </div>
                        <input
                          type="checkbox"
                          checked={formData.email_notifications}
                          onChange={e => handleChange('email_notifications', e.target.checked)}
                          className="rounded border-gray-300 text-primary focus:ring-primary h-5 w-5"
                        />
                      </label>

                      <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                          <span className="text-sm font-medium text-gray-700">SMS Notifications</span>
                          <p className="text-xs text-gray-500 mt-1">
                            Send SMS alerts for important updates (requires SMS provider)
                          </p>
                        </div>
                        <input
                          type="checkbox"
                          checked={formData.sms_notifications}
                          onChange={e => handleChange('sms_notifications', e.target.checked)}
                          className="rounded border-gray-300 text-primary focus:ring-primary h-5 w-5"
                        />
                      </label>
                    </div>

                    <div className="pt-4 border-t border-gray-100">
                      <div className="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                        <div className="flex items-start">
                          <Icons.WarningTriangle />
                          <div className="ml-3">
                            <h4 className="text-sm font-medium text-yellow-800">Email Configuration</h4>
                            <p className="text-xs text-yellow-600 mt-1">
                              Ensure your SMTP settings are correctly configured in the server
                              environment for email notifications to work.
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Save Footer */}
            {hasChanges && (
              <div className="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <p className="text-sm text-gray-500">You have unsaved changes</p>
                <div className="flex gap-3">
                  <Button
                    variant="secondary"
                    onClick={() => {
                      setFormData(data.settings);
                      setHasChanges(false);
                    }}
                  >
                    Discard
                  </Button>
                  <Button onClick={handleSave} loading={isSaving}>
                    Save Changes
                  </Button>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
