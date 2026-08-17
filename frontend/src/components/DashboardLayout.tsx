import { useState } from "react";
import { Outlet } from "react-router-dom";

import Sidebar from "./Sidebar";
import Header from "./Header";

import "../css/DashboardLayout.css";

export default function DashboardLayout() {
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);

    const openSidebar = () => {
        setIsSidebarOpen(true);
    };

    const closeSidebar = () => {
        setIsSidebarOpen(false);
    };

    return (
        <div className="dashboard-layout">

            <Sidebar
                isOpen={isSidebarOpen}
                onClose={closeSidebar}
            />

            {isSidebarOpen && (
                <div
                    className="sidebar-overlay"
                    onClick={closeSidebar}
                />
            )}

            <div className="dashboard-main">

                <Header onMenuClick={openSidebar} />

                <main className="dashboard-content">
                    <Outlet />
                </main>

            </div>

        </div>
    );
}