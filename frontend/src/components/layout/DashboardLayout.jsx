'use client';
import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { AuthProvider, useAuth } from '@/hooks/useAuth';
import Sidebar from '@/components/layout/Sidebar';
import styles from './DashboardLayout.module.css';

function DashboardContent({ children, allowedRoles }) {
    const { user, loading } = useAuth();
    const router = useRouter();
    const [isCollapsed, setIsCollapsed] = useState(false); // Desktop state
    const [isMobileOpen, setIsMobileOpen] = useState(false); // Mobile state
    const [isMobile, setIsMobile] = useState(false);

    // Initial check for mobile
    useEffect(() => {
        const checkMobile = () => {
            setIsMobile(window.innerWidth <= 768);
            if (window.innerWidth <= 768) {
                setIsCollapsed(false); // Reset collapsed on mobile
            }
        };

        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    useEffect(() => {
        if (!loading && !user) {
            router.push('/login');
        } else if (!loading && user && allowedRoles && !allowedRoles.includes(user.role)) {
            const redirectPath = user.role === 'OWNER' ? '/owner' : '/kasir';
            router.push(redirectPath);
        }
    }, [user, loading, router, allowedRoles]);

    if (loading) {
        return (
            <div className={styles.loading}>
                <div className="spinner spinner-lg"></div>
                <p>Memuat...</p>
            </div>
        );
    }

    if (!user) {
        return null;
    }

    return (
        <div className={styles.layout}>
            {/* Mobile Header */}
            <header className={styles.mobileHeader}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                    <button
                        className={styles.mobileToggle}
                        onClick={() => setIsMobileOpen(true)}
                    >
                        ☰
                    </button>
                    <span style={{ fontWeight: 700, fontSize: '1.2rem', color: 'var(--color-primary)' }}>LaundryKu</span>
                </div>
            </header>

            <Sidebar
                isCollapsed={isCollapsed}
                isMobile={isMobile}
                isOpen={isMobileOpen}
                onToggle={() => setIsCollapsed(!isCollapsed)}
                onClose={() => setIsMobileOpen(false)}
            />

            <main className={`${styles.main} ${isCollapsed ? styles.collapsed : ''}`}>
                {children}
            </main>
        </div>
    );
}

export default function DashboardLayout({ children, allowedRoles }) {
    return (
        <AuthProvider>
            <DashboardContent allowedRoles={allowedRoles}>
                {children}
            </DashboardContent>
        </AuthProvider>
    );
}
