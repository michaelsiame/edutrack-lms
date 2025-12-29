
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Badge, Icons, Spinner, Modal, Button, EmptyState } from '../components/Shared';

export const Certificates = () => {
  const { data, isLoading, actions } = useAdmin();
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('all');
  const [showIssueModal, setShowIssueModal] = useState(false);
  const [selectedEnrollment, setSelectedEnrollment] = useState<number | null>(null);
  const [isIssuing, setIsIssuing] = useState(false);

  if (isLoading) return <Spinner />;

  // Filter completed enrollments that don't have certificates yet
  const completedEnrollments = data.enrollments.filter(e =>
    (e.status === 'Completed' || e.status === 'completed') && e.progress >= 100
  );

  // Filter certificates
  const filteredCertificates = data.certificates.filter(cert => {
    const matchesSearch =
      cert.code?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      cert.student?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      cert.course?.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesStatus = filterStatus === 'all' ||
      (filterStatus === 'verified' && cert.verified) ||
      (filterStatus === 'pending' && !cert.verified);

    return matchesSearch && matchesStatus;
  });

  const handleDownload = (certificateId: number) => {
    actions.downloadCertificate(certificateId);
  };

  const handleIssueCertificate = async () => {
    if (!selectedEnrollment) return;

    setIsIssuing(true);
    const success = await actions.issueCertificate(selectedEnrollment);
    setIsIssuing(false);

    if (success) {
      setShowIssueModal(false);
      setSelectedEnrollment(null);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-800">Certificates</h2>
          <p className="text-sm text-gray-500 mt-1">Manage and issue course completion certificates</p>
        </div>
        <Button onClick={() => setShowIssueModal(true)}>
          <Icons.Plus />
          <span className="ml-2">Issue Certificate</span>
        </Button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Total Issued</p>
              <p className="text-2xl font-bold text-gray-800">{data.certificates.length}</p>
            </div>
            <div className="p-3 bg-blue-50 rounded-full text-blue-600">
              <Icons.Award />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Verified</p>
              <p className="text-2xl font-bold text-green-600">
                {data.certificates.filter(c => c.verified).length}
              </p>
            </div>
            <div className="p-3 bg-green-50 rounded-full text-green-600">
              <Icons.CheckCircle />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Pending Verification</p>
              <p className="text-2xl font-bold text-yellow-600">
                {data.certificates.filter(c => !c.verified).length}
              </p>
            </div>
            <div className="p-3 bg-yellow-50 rounded-full text-yellow-600">
              <Icons.ExclamationCircle />
            </div>
          </div>
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1 relative">
            <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <Icons.Search />
            </div>
            <input
              type="text"
              placeholder="Search by code, student, or course..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
          </div>
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white"
          >
            <option value="all">All Status</option>
            <option value="verified">Verified</option>
            <option value="pending">Pending</option>
          </select>
        </div>
      </div>

      {/* Certificates Table */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {filteredCertificates.length === 0 ? (
          <EmptyState
            title="No certificates found"
            description={searchTerm || filterStatus !== 'all'
              ? "Try adjusting your search or filter criteria"
              : "Issue your first certificate to get started"}
            action={
              <Button onClick={() => setShowIssueModal(true)}>
                Issue Certificate
              </Button>
            }
          />
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                  <th className="px-6 py-4">Certificate Code</th>
                  <th className="px-6 py-4">Student</th>
                  <th className="px-6 py-4">Course</th>
                  <th className="px-6 py-4">Issue Date</th>
                  <th className="px-6 py-4">Status</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {filteredCertificates.map((cert: any) => (
                  <tr key={cert.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4">
                      <span className="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded">
                        {cert.code}
                      </span>
                    </td>
                    <td className="px-6 py-4 font-medium text-gray-900">{cert.student}</td>
                    <td className="px-6 py-4 text-sm text-gray-600">{cert.course}</td>
                    <td className="px-6 py-4 text-sm text-gray-500">{cert.date}</td>
                    <td className="px-6 py-4">
                      <Badge color={cert.verified ? 'green' : 'yellow'}>
                        {cert.verified ? 'Verified' : 'Pending'}
                      </Badge>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => handleDownload(cert.id)}
                        className="inline-flex items-center text-primary hover:text-blue-800 text-sm font-medium transition-colors"
                      >
                        <Icons.Download />
                        <span className="ml-1">Download</span>
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Issue Certificate Modal */}
      <Modal
        isOpen={showIssueModal}
        onClose={() => { setShowIssueModal(false); setSelectedEnrollment(null); }}
        title="Issue New Certificate"
      >
        <div className="space-y-4">
          <p className="text-sm text-gray-600">
            Select a completed enrollment to issue a certificate.
          </p>

          {completedEnrollments.length === 0 ? (
            <div className="text-center py-8">
              <div className="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                <Icons.Document />
              </div>
              <p className="text-sm text-gray-500">
                No completed enrollments available for certificate issuance.
              </p>
            </div>
          ) : (
            <div className="space-y-2 max-h-64 overflow-y-auto">
              {completedEnrollments.map(enrollment => (
                <label
                  key={enrollment.id}
                  className={`flex items-center p-3 border rounded-lg cursor-pointer transition-colors ${
                    selectedEnrollment === enrollment.id
                      ? 'border-primary bg-blue-50'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <input
                    type="radio"
                    name="enrollment"
                    checked={selectedEnrollment === enrollment.id}
                    onChange={() => setSelectedEnrollment(enrollment.id)}
                    className="mr-3"
                  />
                  <div className="flex-1">
                    <p className="font-medium text-gray-900">{enrollment.user_name}</p>
                    <p className="text-sm text-gray-500">{enrollment.course_title}</p>
                  </div>
                  <Badge color="green">Completed</Badge>
                </label>
              ))}
            </div>
          )}

          <div className="flex justify-end gap-3 pt-4 border-t">
            <Button
              variant="secondary"
              onClick={() => { setShowIssueModal(false); setSelectedEnrollment(null); }}
            >
              Cancel
            </Button>
            <Button
              onClick={handleIssueCertificate}
              disabled={!selectedEnrollment}
              loading={isIssuing}
            >
              Issue Certificate
            </Button>
          </div>
        </div>
      </Modal>
    </div>
  );
};
