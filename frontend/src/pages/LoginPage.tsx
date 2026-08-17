import { useState } from "react";
import {Link, useNavigate } from "react-router-dom";
import { login } from "../api/authApi";
import "../css/LoginPage.css";

export default function LoginPage() {
    const navigate = useNavigate();

    const [loading, setLoading] = useState(false);
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        setLoading(true);

        try {
            setError("");

            const response = await login({
                email,
                password,
            });

            console.log(response);

            localStorage.setItem("token", response.token);

            navigate("/dashboard");
        } catch (error) {
            console.error(error);
            setError("Invalid email or password");
        }finally {
          setLoading(false);
        }
    };

    return (
        <div className="login-page">
            <div className="login-card">

                <div className="login-header">
                    <h1>Welcome back</h1>
                    <p>Sign in to access your notes</p>
                </div>

                {error && (
                    <div className="login-error">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit}>

                    <div className="form-group">
                        <label className="log" htmlFor="email">
                            Email
                        </label>

                        <input
                            id="email"
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="john@example.com"
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label className="log" htmlFor="password">
                            Password
                        </label>

                        <input
                            id="password"
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="••••••••"
                            required
                        />
                    </div>

                    <button
                        className="login-button"
                        type="submit"
                        disabled={loading}
                    >
                        {loading ? "Logging in..." : "Login"}
                    </button>

                </form>

                <div className="login-register">
                    Don't have an account?{" "}
                    <Link to="/register">
                        Create account
                    </Link>
                </div>

            </div>
        </div>
    );
}