
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Spinner } from '../components/Shared';

export const Settings = () => {
  const { data, actions, isLoading } = useAdmin();
  const [formData, setFormData] = useState<any>(null);

  React.useEffect(() => {
    if (data.settings) setFormData(data.settings);
  }, [data.settings]);

  if (isLoading || !formData) return <Spinner />;

  const handleSave = async () => {
    await actions.updateSettings(formData);
    alert('Settings Saved!');
  };

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-gray-800">System Settings</h2>
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl">
         <div className="space-y-6">
            <div>
               <label className="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
               <input 
                 type="text" 
                 className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" 
                 value={formData.site_name}
                 onChange={e => setFormData({...formData, site_name: e.target.value})}
               />
            </div>
            <div>
               <label className="block text-sm font-medium text-gray-700 mb-1">Registration Fee ({formData.currency})</label>
               <input 
                 type="number" 
                 className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" 
                 value={formData.registration_fee}
                 onChange={e => setFormData({...formData, registration_fee: parseFloat(e.target.value)})}
               />
            </div>
            <div className="flex items-center">
                <input 
                  type="checkbox" 
                  checked={formData.allow_partial_payments}
                  onChange={e => setFormData({...formData, allow_partial_payments: e.target.checked})}
                  className="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                />
                <span className="ml-2 text-sm text-gray-700">Allow Partial Payments</span>
            </div>
            <div className="pt-4 border-t border-gray-100 flex items-center">
               <button 
                 onClick={handleSave} 
                 className="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-blue-600 transition-colors"
               >
                 Save Changes
               </button>
            </div>
         </div>
      </div>
    </div>
  );
};
