
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Badge, Icons, Modal, Spinner } from '../components/Shared';

export const Financials = () => {
  const { data, actions, isLoading } = useAdmin();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [newTx, setNewTx] = useState({ student_id: 0, amount: '', method: 'Cash', type: 'Course Fee', status: 'Completed' });

  if (isLoading) return <Spinner />;

  const pendingAmount = data.transactions
    .filter((t: any) => t.status === 'Pending')
    .reduce((sum: number, t: any) => sum + t.amount, 0);

  const students = data.users.filter((u: any) => u.role === 'Student');

  const handleRecordPayment = async (e: React.FormEvent) => {
    e.preventDefault();
    const student = students.find((s: any) => s.id === Number(newTx.student_id));
    if (student) {
      await actions.addTransaction({
        student: student.name,
        amount: parseFloat(newTx.amount),
        date: new Date().toISOString().split('T')[0],
        status: newTx.status,
        method: newTx.method,
        type: newTx.type
      });
      setIsModalOpen(false);
      setNewTx({ student_id: 0, amount: '', method: 'Cash', type: 'Course Fee', status: 'Completed' });
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-800">Financial Management</h2>
        <button onClick={() => setIsModalOpen(true)} className="flex items-center px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
          <Icons.Plus />
          <span className="ml-2">Record Payment</span>
        </button>
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
         <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-yellow-400">
            <h3 className="text-gray-500 font-medium text-sm">Pending Verification</h3>
            <p className="text-3xl font-bold text-gray-800 mt-2">{data.settings.currency} {pendingAmount.toFixed(2)}</p>
            <p className="text-xs text-yellow-600 mt-1">Action required</p>
         </div>
         <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-green-400">
            <h3 className="text-gray-500 font-medium text-sm">Total Revenue (Completed)</h3>
            <p className="text-3xl font-bold text-gray-800 mt-2">
              {data.settings.currency} {data.transactions.filter((t:any) => t.status === 'Completed').reduce((sum:any, t:any) => sum + t.amount, 0).toFixed(2)}
            </p>
            <p className="text-xs text-green-600 mt-1">All time</p>
         </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-100">
          <h3 className="font-semibold text-gray-800">Transaction History</h3>
        </div>
        <table className="w-full text-left">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th className="px-6 py-4">ID</th>
              <th className="px-6 py-4">Student</th>
              <th className="px-6 py-4">Type</th>
              <th className="px-6 py-4">Method</th>
              <th className="px-6 py-4">Amount</th>
              <th className="px-6 py-4">Status</th>
              <th className="px-6 py-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {data.transactions.map((trx: any) => (
              <tr key={trx.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 text-xs font-mono text-gray-500">{trx.id}</td>
                <td className="px-6 py-4 text-sm font-medium">{trx.student}</td>
                <td className="px-6 py-4 text-sm text-gray-500">{trx.type}</td>
                <td className="px-6 py-4 text-sm text-gray-500">{trx.method}</td>
                <td className="px-6 py-4 text-sm font-bold text-gray-900">{data.settings.currency} {trx.amount}</td>
                <td className="px-6 py-4">
                  <Badge color={trx.status === 'Completed' ? 'green' : trx.status === 'Pending' ? 'yellow' : 'red'}>
                    {trx.status}
                  </Badge>
                </td>
                <td className="px-6 py-4 text-right">
                  {trx.status === 'Pending' && (
                    <button 
                      onClick={() => actions.verifyPayment(trx.id)}
                      className="text-xs bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200 transition-colors font-medium"
                    >
                      Approve
                    </button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Record Manual Payment">
        <form onSubmit={handleRecordPayment} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Student</label>
            <select required className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={newTx.student_id} onChange={e => setNewTx({...newTx, student_id: Number(e.target.value)})}>
              <option value={0}>-- Select Student --</option>
              {students.map((s: any) => (
                <option key={s.id} value={s.id}>{s.name}</option>
              ))}
            </select>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700">Amount ({data.settings.currency})</label>
              <input required type="number" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={newTx.amount} onChange={e => setNewTx({...newTx, amount: e.target.value})} />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Method</label>
              <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={newTx.method} onChange={e => setNewTx({...newTx, method: e.target.value})}>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Mobile Money">Mobile Money</option>
              </select>
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700">Type</label>
              <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={newTx.type} onChange={e => setNewTx({...newTx, type: e.target.value})}>
                <option value="Course Fee">Course Fee</option>
                <option value="Registration">Registration</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Status</label>
              <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={newTx.status} onChange={e => setNewTx({...newTx, status: e.target.value})}>
                <option value="Completed">Completed</option>
                <option value="Pending">Pending</option>
              </select>
            </div>
          </div>
          <div className="mt-5 sm:mt-6">
            <button type="submit" className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:text-sm">
              Save Transaction
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
};
