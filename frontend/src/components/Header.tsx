import "../css/Header.css";

interface HeaderProps {
    onMenuClick: () => void;
}

export default function Header({
    onMenuClick,
}: HeaderProps) {

    return (
        <header className="dashboard-header">

            <div className="header-left">

                {/* Mobile menu */}
                <button
                    type="button"
                    className="menu-button"
                    onClick={onMenuClick}
                    aria-label="Open sidebar"
                >
                    ☰
                </button>

                <div>
                    <h1>Notes Dashboard</h1>
                </div>

            </div>

            <div className="header-actions">
                <span>Welcome back</span>
            </div>

        </header>
    );
}