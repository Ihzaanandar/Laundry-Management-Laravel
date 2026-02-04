'use client';
import { useState, useEffect } from 'react';
import DashboardLayout from '@/components/layout/DashboardLayout';
import api from '@/lib/api';
import toast from 'react-hot-toast';
import styles from './pelanggan.module.css';

export default function PelangganPage() {
    const [customers, setCustomers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingCustomer, setEditingCustomer] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [formData, setFormData] = useState({
        name: '',
        phone: '',
        address: '',
    });

    useEffect(() => {
        loadCustomers();
    }, []);

    const loadCustomers = async () => {
        try {
            const response = await api.getCustomers();
            if (response.success) {
                setCustomers(response.data);
            }
        } catch (error) {
            toast.error('Gagal memuat data pelanggan');
        } finally {
            setLoading(false);
        }
    };

    const openModal = (customer = null) => {
        if (customer) {
            setEditingCustomer(customer);
            setFormData({
                name: customer.name,
                phone: customer.phone || '',
                address: customer.address || '',
            });
        } else {
            setEditingCustomer(null);
            setFormData({ name: '', phone: '', address: '' });
        }
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        setEditingCustomer(null);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!formData.name) {
            toast.error('Nama pelanggan harus diisi');
            return;
        }

        try {
            if (editingCustomer) {
                await api.updateCustomer(editingCustomer.id, formData);
                toast.success('Pelanggan berhasil diperbarui');
            } else {
                await api.createCustomer(formData);
                toast.success('Pelanggan berhasil ditambahkan');
            }
            closeModal();
            loadCustomers();
        } catch (error) {
            toast.error(error.message || 'Gagal menyimpan pelanggan');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Yakin ingin menghapus pelanggan ini?')) return;

        try {
            await api.deleteCustomer(id);
            toast.success('Pelanggan berhasil dihapus');
            loadCustomers();
        } catch (error) {
            toast.error(error.message || 'Gagal menghapus pelanggan. Pelanggan mungkin memiliki order.');
        }
    };

    const filteredCustomers = customers.filter(c =>
        c.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (c.phone && c.phone.includes(searchQuery)) ||
        (c.address && c.address.toLowerCase().includes(searchQuery.toLowerCase()))
    );

    if (loading) {
        return (
            <DashboardLayout allowedRoles={['OWNER']}>
                <div className={styles.loading}>
                    <div className="spinner spinner-lg"></div>
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout allowedRoles={['OWNER']}>
            <div className="page-content">
                <div className="page-header">
                    <h1 className="page-title">Manajemen Pelanggan</h1>
                    <button onClick={() => openModal()} className="btn btn-secondary">
                        ➕ Tambah Pelanggan
                    </button>
                </div>

                {/* Search */}
                <div className="card" style={{ marginBottom: '1.5rem', padding: '1rem' }}>
                    <input
                        type="text"
                        className="form-input"
                        placeholder="🔍 Cari nama, telepon, atau alamat..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                </div>

                <div className="card">
                    <div className="table-container">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>Nama Pelanggan</th>
                                    <th>No. Telepon</th>
                                    <th>Alamat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredCustomers.length > 0 ? (
                                    filteredCustomers.map((customer) => (
                                        <tr key={customer.id}>
                                            <td className="font-semibold">{customer.name}</td>
                                            <td>{customer.phone || '-'}</td>
                                            <td>{customer.address || '-'}</td>
                                            <td>
                                                <div className={styles.actions}>
                                                    <button onClick={() => openModal(customer)} className="btn btn-ghost btn-sm">
                                                        ✏️
                                                    </button>
                                                    <button onClick={() => handleDelete(customer.id)} className="btn btn-ghost btn-sm text-danger">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4" style={{ textAlign: 'center', padding: '2rem' }}>
                                            {searchQuery ? 'Tidak ada pelanggan yang cocok' : 'Belum ada data pelanggan'}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Modal */}
                {showModal && (
                    <div className="modal-overlay" onClick={closeModal}>
                        <div className="modal" onClick={(e) => e.stopPropagation()}>
                            <div className="modal-header">
                                <h3 className="modal-title">{editingCustomer ? 'Edit Pelanggan' : 'Tambah Pelanggan'}</h3>
                                <button onClick={closeModal} className="modal-close">✕</button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body">
                                    <div className="form-group">
                                        <label className="form-label">Nama Pelanggan *</label>
                                        <input
                                            type="text"
                                            className="form-input"
                                            value={formData.name}
                                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                            placeholder="Nama lengkap"
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">No. Telepon</label>
                                        <input
                                            type="text"
                                            className="form-input"
                                            value={formData.phone}
                                            onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                                            placeholder="08xxxxxxxxxx"
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Alamat</label>
                                        <textarea
                                            className="form-input"
                                            value={formData.address}
                                            onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                                            placeholder="Alamat lengkap"
                                            rows={3}
                                        />
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" onClick={closeModal} className="btn btn-ghost">
                                        Batal
                                    </button>
                                    <button type="submit" className="btn btn-primary">
                                        {editingCustomer ? 'Simpan' : 'Tambah'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
