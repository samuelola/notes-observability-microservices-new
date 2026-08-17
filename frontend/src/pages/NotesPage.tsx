import { useEffect, useState } from "react";
import { getNotes,deleteNote, type Note, type Pagination } from "../api/notesApi";
import { useNavigate } from "react-router-dom";

import "../css/NotesPage.css";

export default function NotesPage() {
    const navigate = useNavigate();
    const [notes, setNotes] = useState<Note[]>([]);
    const [deletingId, setDeletingId] = useState<number | null>(null);
    const [pagination, setPagination] =
        useState<Pagination | null>(null);

    const [page, setPage] = useState(1);

    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        const fetchNotes = async () => {
            try {
                setLoading(true);
                setError("");

                const data = await getNotes(page);

                setNotes(data.data);
                setPagination(data.pagination);
            } catch (error) {
                console.error("Failed to fetch notes:", error);

                setError(
                    "Unable to load your notes."
                );
            } finally {
                setLoading(false);
            }
        };

        fetchNotes();
    }, [page]);

     const handleDelete = async (id: number) => {
            const confirmed = window.confirm(
                "Are you sure you want to delete this note?"
            );

            if (!confirmed) {
                return;
            }

            try {
                setDeletingId(id);

                await deleteNote(id);

                // Remove deleted note immediately
                setNotes((currentNotes) =>
                    currentNotes.filter(
                        (note) => note.id !== id
                    )
                );

            } catch (error: any) {
                console.error(
                    "Failed to delete note:",
                    error
                );

                console.error(
                    "Response:",
                    error?.response?.data
                );

                console.error(
                    "Status:",
                    error?.response?.status
                );

                setError(
                    error?.response?.data?.message ||
                    "Unable to delete the note."
                );
            }
            
            finally {
                setDeletingId(null);
            }
        };

    const handlePrevious = () => {
        if (page > 1) {
            setPage((currentPage) => currentPage - 1);
        }
    };

    const handleNext = () => {
        if (
            pagination &&
            pagination.has_more_pages
        ) {
            setPage((currentPage) => currentPage + 1);
        }
    };

    if (loading) {
        return (
            <div className="notes-loading">
                <div className="loader"></div>

                <p>
                    Loading your notes...
                </p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="notes-error">
                {error}
            </div>
        );
    }

    return (
        <div className="notes-page">

            <div className="notes-page-header">
                <div>
                    <h2>My Notes</h2>

                    <p>
                        Manage all your notes from here.
                    </p>
                </div>
            </div>

            {notes.length === 0 ? (
                <div className="empty-notes">
                    <h3>No notes found</h3>

                    <p>
                        You haven't created any notes yet.
                    </p>
                </div>
            ) : (
                <>
                    <div className="notes-grid">

                        {notes.map((note) => (
                            <article
                                className="note-card"
                                key={note.id}
                            >

                                {note.image_url && (
                                    <div className="note-image-wrapper">
                                        <img
                                            src={note.image_url}
                                            alt={note.title}
                                            className="note-image"
                                        />
                                    </div>
                                )}

                                <div className="note-card-body">

                                    <h3>
                                        {note.title}
                                    </h3>

                                    <p>
                                        {note.content}
                                    </p>

                                    <div className="note-actions">

                                        <button
                                            type="button"
                                            onClick={() =>
                                                navigate(`/dashboard/notes/${note.id}/edit`)
                                            }
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            className="delete-button"
                                            onClick={() => handleDelete(note.id)}
                                            disabled={deletingId === note.id}
                                        >
                                            {deletingId === note.id
                                                ? "Deleting..."
                                                : "Delete"}
                                        </button>

                                    </div>

                                </div>

                            </article>
                        ))}

                    </div>

                    {/* Pagination */}
                    {pagination && (
                        <div className="pagination">

                            <button
                                type="button"
                                onClick={handlePrevious}
                                disabled={page === 1}
                            >
                                Previous
                            </button>

                            <span>
                                Page {pagination.current_page}{" "}
                                of {pagination.last_page}
                            </span>

                            <button
                                type="button"
                                onClick={handleNext}
                                disabled={
                                    !pagination.has_more_pages
                                }
                            >
                                Next
                            </button>

                        </div>
                    )}

                </>
            )}

        </div>
    );
}