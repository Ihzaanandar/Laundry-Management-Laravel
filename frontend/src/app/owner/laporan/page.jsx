'use client';
import { useState, useEffect } from 'react';
import DashboardLayout from '@/components/layout/DashboardLayout';
import api from '@/lib/api';
import { formatCurrency, formatDateTime, getStatusLabel, getPaymentStatusLabel } from '@/lib/utils';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import toast from 'react-hot-toast';
import styles from './laporan.module.css';

export default function LaporanPage() {
    const [reportType, setReportType] = useState('daily');
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState({
        date: new Date().toISOString().split('T')[0],
        month: new Date().getMonth() + 1,
        year: new Date().getFullYear(),
        startDate: '',
        endDate: '',
    });

    useEffect(() => {
        loadReport();
    }, [reportType, filters]);

    const loadReport = async () => {
        setLoading(true);
        try {
            let response;
            switch (reportType) {
                case 'daily':
                    response = await api.getDailyReport(filters.date);
                    break;
                case 'monthly':
                    response = await api.getMonthlyReport(filters.month, filters.year);
                    break;
                case 'yearly':
                    response = await api.getYearlyReport(filters.year);
                    break;
                case 'custom':
                    if (filters.startDate && filters.endDate) {
                        response = await api.getCustomReport(filters.startDate, filters.endDate);
                    }
                    break;
            }
            if (response?.success) {
                setData(response.data);
            }
        } catch (error) {
            toast.error('Gagal memuat laporan');
        } finally {
            setLoading(false);
        }
    };

    const handleExport = (format) => {
        if (!data || !data.orders || data.orders.length === 0) {
            toast.error('Tidak ada data untuk diekspor');
            return;
        }

        if (format === 'excel') {
            exportToExcel();
        } else {
            exportToPdf();
        }
    };

    const exportToExcel = () => {
        // Create CSV content (Excel compatible)
        const headers = ['No. Nota', 'Pelanggan', 'Total', 'Status', 'Pembayaran', 'Waktu'];
        const rows = data.orders.map(order => [
            order.orderNumber,
            order.customer?.name || '-',
            order.totalAmount,
            getStatusLabel(order.status),
            getPaymentStatusLabel(order.paymentStatus),
            formatDateTime(order.createdAt)
        ]);

        // Add summary row
        rows.push([]);
        rows.push(['RINGKASAN']);
        rows.push(['Total Pendapatan', formatCurrency(data.totalRevenue || 0)]);
        rows.push(['Total Transaksi', data.totalOrders || 0]);
        rows.push(['Transaksi Lunas', data.paidOrders || 0]);
        rows.push(['Belum Bayar', data.unpaidOrders || 0]);

        // Convert to CSV
        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
        ].join('\n');

        // Download file
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `laporan_${reportType}_${filters.date || filters.year}.csv`;
        link.click();
        toast.success('File Excel berhasil diunduh');
    };

    const exportToPdf = () => {
        // Create printable HTML
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Laporan Keuangan</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h1 { color: #1e3a5f; border-bottom: 2px solid #e67e22; padding-bottom: 10px; }
                    .summary { display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
                    .summary-item { background: #f5f5f5; padding: 15px; border-radius: 8px; min-width: 150px; }
                    .summary-value { font-size: 24px; font-weight: bold; color: #1e3a5f; }
                    .summary-label { color: #666; font-size: 12px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                    th { background: #1e3a5f; color: white; }
                    tr:nth-child(even) { background: #f9f9f9; }
                    .lunas { color: green; }
                    .belum { color: orange; }
                    .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <h1>Laporan Keuangan - ${reportType === 'daily' ? 'Harian' : reportType === 'monthly' ? 'Bulanan' : 'Tahunan'}</h1>
                <p>Periode: ${filters.date || `${filters.month}/${filters.year}` || filters.year}</p>
                
                <div class="summary">
                    <div class="summary-item">
                        <div class="summary-value">${formatCurrency(data.totalRevenue || 0)}</div>
                        <div class="summary-label">Total Pendapatan</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">${data.totalOrders || 0}</div>
                        <div class="summary-label">Total Transaksi</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">${data.paidOrders || 0}</div>
                        <div class="summary-label">Transaksi Lunas</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">${data.unpaidOrders || 0}</div>
                        <div class="summary-label">Belum Bayar</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>No. Nota</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.orders.map(order => `
                            <tr>
                                <td>${order.orderNumber}</td>
                                <td>${order.customer?.name || '-'}</td>
                                <td>${formatCurrency(order.totalAmount)}</td>
                                <td>${getStatusLabel(order.status)}</td>
                                <td class="${order.paymentStatus === 'SUDAH_BAYAR' ? 'lunas' : 'belum'}">
                                    ${getPaymentStatusLabel(order.paymentStatus)}
                                </td>
                                <td>${formatDateTime(order.createdAt)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <div class="footer">
                    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                    <p>LaundryKu - Sistem Manajemen Laundry</p>
                </div>
            </body>
            </html>
        `;

        // Open print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
        }, 250);
        toast.success('Silakan simpan sebagai PDF dari dialog print');
    };

    return (
        <DashboardLayout allowedRoles={['OWNER']}>
            <div className="page-content">
                <div className="page-header">
                    <h1 className="page-title">Laporan Keuangan</h1>
                    <div className={styles.exportBtns}>
                        <button onClick={() => handleExport('excel')} className="btn btn-outline btn-sm">
                            📊 Export Excel
                        </button>
                        <button onClick={() => handleExport('pdf')} className="btn btn-outline btn-sm">
                            📄 Export PDF
                        </button>
                    </div>
                </div>

                {/* Filters */}
                <div className={`card ${styles.filterCard}`}>
                    <div className={styles.tabs}>
                        {[
                            { id: 'daily', label: 'Harian' },
                            { id: 'monthly', label: 'Bulanan' },
                            { id: 'yearly', label: 'Tahunan' },
                            { id: 'custom', label: 'Custom' },
                        ].map((tab) => (
                            <button
                                key={tab.id}
                                className={`${styles.tab} ${reportType === tab.id ? styles.active : ''}`}
                                onClick={() => setReportType(tab.id)}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    <div className={styles.filterRow}>
                        {reportType === 'daily' && (
                            <div className="form-group">
                                <label className="form-label">Tanggal</label>
                                <input
                                    type="date"
                                    className="form-input"
                                    value={filters.date}
                                    onChange={(e) => setFilters({ ...filters, date: e.target.value })}
                                />
                            </div>
                        )}
                        {reportType === 'monthly' && (
                            <>
                                <div className="form-group">
                                    <label className="form-label">Bulan</label>
                                    <select
                                        className="form-input form-select"
                                        value={filters.month}
                                        onChange={(e) => setFilters({ ...filters, month: parseInt(e.target.value) })}
                                    >
                                        {Array.from({ length: 12 }, (_, i) => (
                                            <option key={i + 1} value={i + 1}>
                                                {new Date(2024, i).toLocaleString('id-ID', { month: 'long' })}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label className="form-label">Tahun</label>
                                    <select
                                        className="form-input form-select"
                                        value={filters.year}
                                        onChange={(e) => setFilters({ ...filters, year: parseInt(e.target.value) })}
                                    >
                                        {Array.from({ length: 5 }, (_, i) => (
                                            <option key={i} value={new Date().getFullYear() - i}>
                                                {new Date().getFullYear() - i}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </>
                        )}
                        {reportType === 'yearly' && (
                            <div className="form-group">
                                <label className="form-label">Tahun</label>
                                <select
                                    className="form-input form-select"
                                    value={filters.year}
                                    onChange={(e) => setFilters({ ...filters, year: parseInt(e.target.value) })}
                                >
                                    {Array.from({ length: 5 }, (_, i) => (
                                        <option key={i} value={new Date().getFullYear() - i}>
                                            {new Date().getFullYear() - i}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}
                        {reportType === 'custom' && (
                            <>
                                <div className="form-group">
                                    <label className="form-label">Dari Tanggal</label>
                                    <input
                                        type="date"
                                        className="form-input"
                                        value={filters.startDate}
                                        onChange={(e) => setFilters({ ...filters, startDate: e.target.value })}
                                    />
                                </div>
                                <div className="form-group">
                                    <label className="form-label">Sampai Tanggal</label>
                                    <input
                                        type="date"
                                        className="form-input"
                                        value={filters.endDate}
                                        onChange={(e) => setFilters({ ...filters, endDate: e.target.value })}
                                    />
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {loading ? (
                    <div className={styles.loading}>
                        <div className="spinner spinner-lg"></div>
                    </div>
                ) : data ? (
                    <>
                        {/* Summary Cards */}
                        <div className="grid grid-4 mt-6">
                            <div className="stat-card">
                                <div className="stat-icon success">💰</div>
                                <div className="stat-content">
                                    <div className="stat-value">{formatCurrency(data.totalRevenue || 0)}</div>
                                    <div className="stat-label">Total Pendapatan</div>
                                </div>
                            </div>
                            <div className="stat-card">
                                <div className="stat-icon primary">📋</div>
                                <div className="stat-content">
                                    <div className="stat-value">{data.totalOrders || 0}</div>
                                    <div className="stat-label">Total Transaksi</div>
                                </div>
                            </div>
                            <div className="stat-card">
                                <div className="stat-icon secondary">✅</div>
                                <div className="stat-content">
                                    <div className="stat-value">{data.paidOrders || 0}</div>
                                    <div className="stat-label">Transaksi Lunas</div>
                                </div>
                            </div>
                            <div className="stat-card">
                                <div className="stat-icon warning">⏳</div>
                                <div className="stat-content">
                                    <div className="stat-value">{data.unpaidOrders || 0}</div>
                                    <div className="stat-label">Belum Bayar</div>
                                </div>
                            </div>
                        </div>

                        {/* Chart for monthly/yearly */}
                        {(reportType === 'monthly' && data.dailyBreakdown?.length > 0) && (
                            <div className="card mt-6">
                                <div className="card-header">
                                    <h3 className="card-title">Pendapatan Harian</h3>
                                </div>
                                <div className={styles.chartContainer}>
                                    <ResponsiveContainer width="100%" height={300}>
                                        <BarChart data={data.dailyBreakdown}>
                                            <CartesianGrid strokeDasharray="3 3" stroke="#eee" />
                                            <XAxis dataKey="date" tick={{ fontSize: 10 }} tickFormatter={(v) => v.split('-')[2]} />
                                            <YAxis tick={{ fontSize: 10 }} tickFormatter={(v) => `${(v / 1000).toFixed(0)}k`} />
                                            <Tooltip formatter={(v) => [formatCurrency(v), 'Pendapatan']} />
                                            <Bar dataKey="revenue" fill="#e67e22" radius={[4, 4, 0, 0]} />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                        )}

                        {(reportType === 'yearly' && data.monthlyBreakdown?.length > 0) && (
                            <div className="card mt-6">
                                <div className="card-header">
                                    <h3 className="card-title">Pendapatan Bulanan</h3>
                                </div>
                                <div className={styles.chartContainer}>
                                    <ResponsiveContainer width="100%" height={300}>
                                        <BarChart data={data.monthlyBreakdown}>
                                            <CartesianGrid strokeDasharray="3 3" stroke="#eee" />
                                            <XAxis dataKey="month" tick={{ fontSize: 12 }} tickFormatter={(m) => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][m - 1]} />
                                            <YAxis tick={{ fontSize: 10 }} tickFormatter={(v) => `${(v / 1000000).toFixed(1)}jt`} />
                                            <Tooltip formatter={(v) => [formatCurrency(v), 'Pendapatan']} />
                                            <Bar dataKey="revenue" fill="#1e3a5f" radius={[4, 4, 0, 0]} />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                        )}

                        {/* Orders Table */}
                        {data.orders?.length > 0 && (
                            <div className="card mt-6">
                                <div className="card-header">
                                    <h3 className="card-title">Detail Transaksi</h3>
                                </div>
                                <div className="table-container">
                                    <table className="table">
                                        <thead>
                                            <tr>
                                                <th>No. Nota</th>
                                                <th>Pelanggan</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Pembayaran</th>
                                                <th>Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {data.orders.map((order) => (
                                                <tr key={order.id}>
                                                    <td className="font-semibold">{order.orderNumber}</td>
                                                    <td>{order.customer.name}</td>
                                                    <td>{formatCurrency(order.totalAmount)}</td>
                                                    <td>
                                                        <span className={`status-badge status-${order.status.toLowerCase()}`}>
                                                            {getStatusLabel(order.status)}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span className={`badge ${order.paymentStatus === 'SUDAH_BAYAR' ? 'badge-success' : 'badge-warning'}`}>
                                                            {getPaymentStatusLabel(order.paymentStatus)}
                                                        </span>
                                                    </td>
                                                    <td className="text-muted">{formatDateTime(order.createdAt)}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </>
                ) : (
                    <div className="card mt-6">
                        <div className="empty-state">
                            <div className="empty-state-icon">📊</div>
                            <div className="empty-state-title">Pilih Periode</div>
                            <div className="empty-state-description">Pilih filter periode untuk melihat laporan</div>
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
