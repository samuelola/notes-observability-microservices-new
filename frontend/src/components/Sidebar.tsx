import { Link, useLocation, useNavigate } from "react-router-dom";

import "../css/Sidebar.css";

interface SidebarProps {
    isOpen: boolean;
    onClose: () => void;
}

export default function Sidebar({
    isOpen,
    onClose,
}: SidebarProps) {

    const location = useLocation();
    const navigate = useNavigate();

    const handleLogout = () => {
        localStorage.removeItem("token");

        onClose();

        navigate("/login");
    };

    const handleNavigation = () => {
        onClose();
    };

    return (
        <aside
            className={`sidebar ${
                isOpen ? "sidebar-open" : ""
            }`}
        >

            {/* Logo/header */}
            <div className="sidebar-logo">

                <div className="sidebar-logo-content">
                    <h2>Notes App</h2>

                    {/* Mobile close button */}
                    <button
                        type="button"
                        className="sidebar-close"
                        onClick={onClose}
                        aria-label="Close sidebar"
                    >
                        ×
                    </button>
                </div>

            </div>

            {/* Navigation */}
            <nav className="sidebar-nav">

                <Link
                    to="/dashboard"
                    onClick={handleNavigation}
                    className={
                        location.pathname === "/dashboard"
                            ? "active"
                            : ""
                    }
                >
                    Dashboard
                </Link>

                <Link
                    to="/dashboard/notes"
                    onClick={handleNavigation}
                    className={
                        location.pathname === "/dashboard/notes"
                            ? "active"
                            : ""
                    }
                >
                    Notes
                </Link>

                <Link
                    to="/dashboard/notes/create"
                    onClick={handleNavigation}
                    className={
                        location.pathname ===
                        "/dashboard/notes/create"
                            ? "active"
                            : ""
                    }
                >
                    Create Note
                </Link>

            </nav>

            {/* Bottom */}
            <div className="sidebar-bottom">

                <button
                    type="button"
                    className="logout-button"
                    onClick={handleLogout}
                >
                    Logout
                </button>

            </div>

        </aside>
    );
}