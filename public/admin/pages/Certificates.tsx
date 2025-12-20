
import React from 'react';
import { useAdmin } from '../context/AdminContext';
import { Badge, Icons, Spinner } from '../components/Shared';

export const Certificates = () => {
  const { data, isLoading } = useAdmin();

  if (isLoading) return <Spinner />;

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-800">Issued Certificates</h2>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th className="px-6 py-4">Certificate Code</th>
              <th className="px-6 py-4">Student</th>
              <th className="px-6 py-4">Course</th>
              <th className="px-6 py-4">Issued Date</th>
              <th className="px-6 py-4">Status</th>
              <th className="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {data.certificates.map((cert: any) => (
              <tr key={cert.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 font-mono text-xs text-gray-600">{cert.code}</td>
                <td className="px-6 py-4 font-medium text-gray-900">{cert.student}</td>
                <td className="px-6 py-4 text-sm text-gray-600">{cert.course}</td>
                <td className="px-6 py-4 text-sm text-gray-500">{cert.date}</td>
                <td className="px-6 py-4">
                  <Badge color={cert.verified ? 'green' : 'gray'}>{cert.verified ? 'Verified' : 'Pending'}</Badge>
                </td>
                <td className="px-6 py-4 text-right">
                  <button className="text-primary hover:text-blue-800 text-sm font-medium">Download</button>
                </td>
              </tr>
            ))}
            {data.certificates.length === 0 && (
                <tr>
                    <td colSpan={6} className="px-6 py-8 text-center text-gray-500">No certificates issued yet.</td>
                </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};
