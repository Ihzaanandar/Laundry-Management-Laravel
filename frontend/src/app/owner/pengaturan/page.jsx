'use client';
import { useState, useEffect } from 'react';
import DashboardLayout from '@/components/layout/DashboardLayout';
import api from '@/lib/api';
import toast from 'react-hot-toast';
import styles from './pengaturan.module.css';

export default function PengaturanPage() {
    const [settings, setSettings] = useState({
        businessName: '',
        address: '',
        phone: '',
        footer: '',
        template: 'simple',
        logoUrl: '',
    });
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await api.getSettings();
            if (response.success) {
                setSettings(response.data);
            }
        } catch (error) {
            toast.error('Gagal memuat pengaturan');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await api.updateSettings(settings);
            toast.success('Pengaturan berhasil disimpan');
        } catch (error) {
            toast.error(error.message || 'Gagal menyimpan pengaturan');
        } finally {
            setSaving(false);
        }
    };

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
                    <h1 className="page-title">Pengaturan Nota</h1>
                </div>

                <div className={styles.container}>
                    <div className="card">
                        <div className="card-header">
                            <h3 className="card-title">Informasi Bisnis</h3>
                        </div>
                        <form onSubmit={handleSubmit}>
                            <div className="form-group">
                                <label className="form-label">Nama Laundry</label>
                                <input
                                    type="text"
                                    className="form-input"
                                    value={settings.businessName}
                                    onChange={(e) => setSettings({ ...settings, businessName: e.target.value })}
                                    placeholder="LaundryKu"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Alamat</label>
                                <textarea
                                    className="form-input"
                                    rows={2}
                                    value={settings.address || ''}
                                    onChange={(e) => setSettings({ ...settings, address: e.target.value })}
                                    placeholder="Jl. Raya Laundry No. 123, Kota"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">No. Telepon</label>
                                <input
                                    type="text"
                                    className="form-input"
                                    value={settings.phone || ''}
                                    onChange={(e) => setSettings({ ...settings, phone: e.target.value })}
                                    placeholder="08123456789"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Footer Nota</label>
                                <textarea
                                    className="form-input"
                                    rows={2}
                                    value={settings.footer || ''}
                                    onChange={(e) => setSettings({ ...settings, footer: e.target.value })}
                                    placeholder="Terima kasih telah menggunakan jasa kami!"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">URL Logo (opsional)</label>
                                <input
                                    type="url"
                                    className="form-input"
                                    value={settings.logoUrl || ''}
                                    onChange={(e) => setSettings({ ...settings, logoUrl: e.target.value })}
                                    placeholder="https://example.com/logo.png"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Template Nota</label>
                                <div className={styles.templateOptions}>
                                    <label className={`${styles.templateOption} ${settings.template === 'simple' ? styles.selected : ''}`}>
                                        <input
                                            type="radio"
                                            name="template"
                                            value="simple"
                                            checked={settings.template === 'simple'}
                                            onChange={(e) => setSettings({ ...settings, template: e.target.value })}
                                        />
                                        <div className={styles.templatePreview}>
                                            <div className={styles.templateIcon}>📄</div>
                                            <span>Sederhana</span>
                                        </div>
                                    </label>
                                    <label className={`${styles.templateOption} ${settings.template === 'professional' ? styles.selected : ''}`}>
                                        <input
                                            type="radio"
                                            name="template"
                                            value="professional"
                                            checked={settings.template === 'professional'}
                                            onChange={(e) => setSettings({ ...settings, template: e.target.value })}
                                        />
                                        <div className={styles.templatePreview}>
                                            <div className={styles.templateIcon}>📋</div>
                                            <span>Profesional</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" className="btn btn-primary btn-lg w-full" disabled={saving}>
                                {saving ? 'Menyimpan...' : 'Simpan Pengaturan'}
                            </button>
                        </form>
                    </div>

                    {/* Preview */}
                    <div className="card">
                        <div className="card-header">
                            <h3 className="card-title">Preview Nota</h3>
                        </div>
                        <div className={styles.notaPreview}>
                            {settings.template === 'simple' ? (
                                /* Simple Template Preview */
                                <div style={{ fontFamily: 'monospace', fontSize: '0.8rem', color: '#000' }}>
                                    <div className="text-center mb-4" style={{ textAlign: 'center', marginBottom: '1rem' }}>
                                        <h2 style={{ fontSize: '1.2rem', fontWeight: 'bold', margin: 0 }}>{settings.businessName || 'Nama Laundry'}</h2>
                                        <p style={{ margin: 0, fontSize: '0.7rem' }}>{new Date().toLocaleDateString('id-ID')}</p>
                                    </div>
                                    <div style={{ borderBottom: '1px dashed #000', margin: '0.5rem 0' }}></div>
                                    <div style={{ fontSize: '0.8rem' }}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                            <span>Nota:</span>
                                            <span>LDR260201XXXX</span>
                                        </div>
                                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                            <span>Plg:</span>
                                            <span>Budi Santoso</span>
                                        </div>
                                    </div>
                                    <div style={{ borderBottom: '1px dashed #000', margin: '0.5rem 0' }}></div>
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.25rem' }}>
                                        <div style={{ marginBottom: '0.25rem' }}>
                                            <div>Cuci Kering Lipat</div>
                                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                                <span>3 kg x Rp 7.000</span>
                                                <span>Rp 21.000</span>
                                            </div>
                                        </div>
                                        <div style={{ marginBottom: '0.25rem' }}>
                                            <div>Setrika</div>
                                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                                <span>2 kg x Rp 5.000</span>
                                                <span>Rp 10.000</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style={{ borderBottom: '1px dashed #000', margin: '0.5rem 0' }}></div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', fontWeight: 'bold' }}>
                                        <span>Total:</span>
                                        <span>Rp 31.000</span>
                                    </div>
                                    <div style={{ borderBottom: '1px dashed #000', margin: '0.5rem 0' }}></div>
                                    <div style={{ textAlign: 'center', marginTop: '1rem', fontSize: '0.7rem' }}>
                                        <p>{settings.footer || 'Terima kasih!'}</p>
                                    </div>
                                </div>
                            ) : (
                                /* Professional Template Preview (Default) */
                                <>
                                    <div className={styles.notaHeader}>
                                        {settings.logoUrl && (
                                            <img src={settings.logoUrl} alt="Logo" className={styles.notaLogo} />
                                        )}
                                        <h2 className={styles.notaTitle}>{settings.businessName || 'Nama Laundry'}</h2>
                                        <p className={styles.notaAddress}>{settings.address || 'Alamat laundry'}</p>
                                        <p className={styles.notaPhone}>{settings.phone || '08xxxxxxxxxx'}</p>
                                    </div>
                                    <div className={styles.notaDivider}></div>
                                    <div className={styles.notaInfo}>
                                        <div>
                                            <span>No. Nota</span>
                                            <strong>LDR260201XXXX</strong>
                                        </div>
                                        <div>
                                            <span>Tanggal</span>
                                            <strong>{new Date().toLocaleDateString('id-ID')}</strong>
                                        </div>
                                    </div>
                                    <div className={styles.notaDivider}></div>
                                    <div className={styles.notaItems}>
                                        <div className={styles.notaItem}>
                                            <span>Cuci Kering Lipat (3 kg)</span>
                                            <span>Rp 21.000</span>
                                        </div>
                                        <div className={styles.notaItem}>
                                            <span>Setrika (2 kg)</span>
                                            <span>Rp 10.000</span>
                                        </div>
                                    </div>
                                    <div className={styles.notaDivider}></div>
                                    <div className={styles.notaTotal}>
                                        <span>Total</span>
                                        <strong>Rp 31.000</strong>
                                    </div>
                                    <div className={styles.notaFooter}>
                                        <p>{settings.footer || 'Terima kasih!'}</p>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
