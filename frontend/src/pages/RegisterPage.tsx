import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";

import { register } from "../api/authApi";
import "../css/RegisterPage.css";

export default function RegisterPage() {
    const navigate = useNavigate();

    const [name, setName] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        setError("");

        setLoading(true);

        try {
            
            const response = await register({
                name,
                email,
                password
            });

            console.log(response);

            // await api.post("/auth/register", {
            //     name,
            //     email,
            //     password,
            //     password_confirmation: passwordConfirmation,
            // });

            // Registration successful
            navigate("/login");
        } catch (error: any) {
            if (error.response?.data?.message) {
                setError(error.response.data.message);
            } else if (error.response?.data?.errors) {
                const errors = error.response.data.errors;

                const firstError = Object.values(errors)
                    .flat()
                    .at(0);

                setError(
                    typeof firstError === "string"
                        ? firstError
                        : "Registration failed."
                );
            } else {
                setError("Registration failed. Please try again.");
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="register-page">
            <div className="register-card">

                <div className="register-header">
                    <h1>Create account</h1>

                    <p>
                        Create your account to start managing your notes.
                    </p>
                </div>

                {error && (
                    <div className="register-error">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit}>

                    <div className="form-group">
                        <label className="reg" htmlFor="name">
                            Name
                        </label>

                        <input
                            id="name"
                            type="text"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            placeholder="John Doe"
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label className="reg" htmlFor="email">
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
                        <label className="reg" htmlFor="password">
                            Password
                        </label>

                        <input
                            id="password"
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="••••••••"
                            minLength={8}
                            required
                        />
                    </div>

                    {/* <div className="form-group">
                        <label className="reg" htmlFor="password_confirmation">
                            Confirm password
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            value={passwordConfirmation}
                            onChange={(e) =>
                                setPasswordConfirmation(e.target.value)
                            }
                            placeholder="••••••••"
                            minLength={8}
                            required
                        />
                    </div> */}

                    <button
                        className="register-button"
                        type="submit"
                        disabled={loading}
                    >
                        {loading ? "Creating account..." : "Create account"}
                    </button>
                </form>

                <div className="register-login">
                    Already have an account?{" "}

                    <Link to="/login">
                        Login
                    </Link>
                </div>
            </div>
        </div>
    );
}