/**
 * Beyond Barista Academy — Main Client JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Theme Switcher (Dark / Light Mode)
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const htmlElement = document.documentElement;

    const getStoredTheme = () => localStorage.getItem('bba_theme') || 'light';
    const setStoredTheme = (theme) => localStorage.setItem('bba_theme', theme);

    const applyTheme = (theme) => {
        htmlElement.setAttribute('data-bs-theme', theme);
        if (themeToggleBtn) {
            const icon = themeToggleBtn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars-fill';
            }
        }
    };

    // Initialize theme
    applyTheme(getStoredTheme());

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const currentTheme = htmlElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setStoredTheme(newTheme);
            applyTheme(newTheme);
        });
    }

    // 2. Global CSRF AJAX Setup
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    window.bbaFetch = async (url, options = {}) => {
        options.headers = options.headers || {};
        if (csrfToken) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        options.headers['X-Requested-With'] = 'XMLHttpRequest';

        try {
            const response = await fetch(url, options);
            const data = await response.json();
            return { ok: response.ok, status: response.status, data };
        } catch (error) {
            console.error('Fetch error:', error);
            return { ok: false, status: 500, data: { success: false, message: 'Network or server error.' } };
        }
    };

    // 3. Mark Lesson Complete via AJAX
    const completeLessonBtn = document.getElementById('btnCompleteLesson');
    if (completeLessonBtn) {
        completeLessonBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const lessonId = completeLessonBtn.getAttribute('data-lesson-id');
            const courseId = completeLessonBtn.getAttribute('data-course-id');
            const nextUrl = completeLessonBtn.getAttribute('data-next-url');

            completeLessonBtn.disabled = true;
            completeLessonBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving progress...';

            const res = await window.bbaFetch(window.BBA_URL + '/api/lesson/complete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lesson_id: lessonId, course_id: courseId })
            });

            if (res.ok && res.data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Lesson Completed!',
                        text: res.data.message || 'Great job! Moving to the next lesson.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (nextUrl) {
                            window.location.href = nextUrl;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    window.location.href = nextUrl || window.location.href;
                }
            } else {
                completeLessonBtn.disabled = false;
                completeLessonBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Mark as Complete';
                alert(res.data.message || 'Error updating progress.');
            }
        });
    }

    // 4. Wishlist Toggle Buttons
    document.querySelectorAll('.btn-wishlist-toggle').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const courseId = btn.getAttribute('data-course-id');
            const res = await window.bbaFetch(window.BBA_URL + '/api/wishlist/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ course_id: courseId })
            });

            if (res.ok && res.data.success) {
                const icon = btn.querySelector('i');
                if (icon) {
                    if (res.data.in_wishlist) {
                        icon.className = 'bi bi-heart-fill text-danger';
                    } else {
                        icon.className = 'bi bi-heart';
                    }
                }
            } else if (res.status === 401) {
                window.location.href = window.BBA_URL + '/login';
            }
        });
    });

    // 5. Interactive Quiz Runner Timer
    const quizTimerEl = document.getElementById('quizTimer');
    if (quizTimerEl) {
        let totalSeconds = parseInt(quizTimerEl.getAttribute('data-seconds'), 10) || 1200;
        const quizForm = document.getElementById('quizForm');

        const timerInterval = setInterval(() => {
            totalSeconds--;
            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                quizTimerEl.textContent = '00:00';
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Time is up!',
                        text: 'Your quiz is being automatically submitted.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        if (quizForm) quizForm.submit();
                    });
                } else {
                    alert('Time is up! Submitting answers.');
                    if (quizForm) quizForm.submit();
                }
            } else {
                const mins = Math.floor(totalSeconds / 60);
                const secs = totalSeconds % 60;
                quizTimerEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                if (totalSeconds < 120) {
                    quizTimerEl.classList.add('text-danger', 'fw-bold');
                }
            }
        }, 1000);
    }
});
