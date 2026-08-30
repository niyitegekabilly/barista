<?php $pageTitle = e($currentLesson['title'] ?? $course['title']); ?>

<div class="classroom-container" id="classroomMainWrapper">
    
    <!-- Left Learning Player Stage -->
    <div class="classroom-player-area">
        <?php if (empty($currentLesson)): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="<?= url('student/courses') ?>" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to My Courses
                    </a>
                    <h3 class="font-heading fw-bold text-dark mt-1 mb-0"><?= e($course['title']) ?></h3>
                </div>
            </div>

            <div class="card p-5 text-center bg-light border-0 shadow-sm rounded-4">
                <i class="bi bi-collection-play display-3 text-primary mb-3 opacity-75"></i>
                <h4 class="font-heading fw-bold text-dark">Course Curriculum Under Preparation</h4>
                <p class="text-muted small max-w-500 mx-auto mb-4">The instructor is finalizing the video lessons, dial-in recipe sheets, and assessment quizzes for this course. Please check back shortly.</p>
                <div>
                    <a href="<?= url('student/courses') ?>" class="btn btn-primary fw-bold px-4">Return to My Courses</a>
                </div>
            </div>
        <?php else: ?>
            
            <!-- Top Lesson Navigation Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <a href="<?= url('student/courses') ?>" class="text-muted small text-decoration-none d-inline-flex align-items-center gap-1 mb-1">
                        <i class="bi bi-arrow-left"></i> Back to My Courses
                    </a>
                    <h3 class="font-heading fw-bold text-dark mb-0"><?= e($currentLesson['title']) ?></h3>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i> <?= e($currentLesson['duration_minutes']) ?> min • Lesson <?= e($currentLesson['sort_order']) ?></small>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <?php if ($prevLesson): ?>
                        <a href="<?= url('student/classroom/' . e($course['slug']) . '/' . e($prevLesson['id'])) ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" title="Previous Lesson">
                            <i class="bi bi-chevron-left"></i> Prev
                        </a>
                    <?php endif; ?>

                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnToggleTheater" title="Theater Mode">
                        <i class="bi bi-aspect-ratio"></i>
                    </button>

                    <?php if ($nextLesson): ?>
                        <a href="<?= url('student/classroom/' . e($course['slug']) . '/' . e($nextLesson['id'])) ?>" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" title="Next Lesson">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cinema Theater Player Stage -->
            <div class="mb-4 position-relative" id="cinemaTheaterStage">
                <?php if (($currentLesson['lesson_type'] ?? 'video') === 'video'): ?>
                    <?= \App\Services\VideoService::renderEmbed(
                        $currentLesson['video_url'] ?? '',
                        $currentLesson['video_provider'] ?? 'auto',
                        $currentLesson['title'] ?? 'Lesson Video'
                    ) ?>
                <?php elseif (($currentLesson['lesson_type'] ?? '') === 'pdf'): ?>
                    <div class="card p-5 text-center bg-light border-0 shadow-sm rounded-4">
                        <i class="bi bi-file-earmark-pdf-fill display-3 text-danger mb-3"></i>
                        <h4 class="font-heading fw-bold text-dark"><?= e($currentLesson['title']) ?></h4>
                        <p class="text-muted small max-w-700 mx-auto mb-4">Official Beyond Barista Academy training manual and extraction dial-in handbook.</p>
                        <?php if (!empty($currentLesson['pdf_path'])): ?>
                            <a href="<?= asset('uploads/' . e($currentLesson['pdf_path'])) ?>" class="btn btn-primary btn-lg fw-bold px-4 shadow" target="_blank" download>
                                <i class="bi bi-download me-1"></i> Download PDF Reference Material
                            </a>
                        <?php else: ?>
                            <p class="text-muted small">Reference material available in the resources tab.</p>
                        <?php endif; ?>
                    </div>
                <?php elseif (($currentLesson['lesson_type'] ?? '') === 'audio'): ?>
                    <div class="card p-4 bg-light border-0 shadow-sm rounded-4 text-center">
                        <i class="bi bi-soundwave display-3 text-primary mb-3"></i>
                        <h5 class="font-heading fw-bold mb-3"><?= e($currentLesson['title']) ?></h5>
                        <?php if (!empty($currentLesson['video_url'])): ?>
                            <audio controls controlsList="nodownload" class="w-100 mb-2" id="classroomAudioPlayer">
                                <source src="<?= e($currentLesson['video_url']) ?>" type="audio/mpeg">
                                Your browser does not support audio playback.
                            </audio>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Controls Strip -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-surface d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnQuickBookmark" title="Bookmark current second">
                        <i class="bi bi-bookmark-plus text-warning me-1"></i> Bookmark Moment
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnQuickNote" title="Take note at current second">
                        <i class="bi bi-pencil-square text-primary me-1"></i> Take Note
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <?php $isCompleted = in_array((int)$currentLesson['id'], $completedLessonIds ?? [], true); ?>
                    <button type="button" class="btn <?= $isCompleted ? 'btn-success' : 'btn-outline-success' ?> fw-bold px-4 shadow-sm" id="btnMarkComplete"
                            data-lesson-id="<?= e($currentLesson['id']) ?>" 
                            data-course-id="<?= e($course['id']) ?>"
                            data-enrollment-id="<?= e($enrollment['id']) ?>"
                            data-next-url="<?= $nextLesson ? url('student/classroom/' . e($course['slug']) . '/' . e($nextLesson['id'])) : '' ?>">
                        <i class="bi <?= $isCompleted ? 'bi-check-circle-fill' : 'bi-check2-circle' ?> me-1"></i>
                        <span id="markCompleteText"><?= $isCompleted ? 'Completed' : 'Mark as Complete' ?></span>
                    </button>
                </div>
            </div>

            <!-- 5-Tab Learning Workspace -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
                <ul class="nav nav-pills gap-2 mb-4 border-bottom pb-3" id="classroomTabs">
                    <li class="nav-item">
                        <a class="nav-link active rounded-pill px-3" data-bs-toggle="pill" href="#tabLessonGuide">
                            <i class="bi bi-book me-1"></i> Lesson Guide
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabMyNotes">
                            <i class="bi bi-journal-text me-1"></i> My Notes (<?= count($notes) ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabResources">
                            <i class="bi bi-paperclip me-1"></i> Resources (<?= count($resources) ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabDiscussions">
                            <i class="bi bi-chat-dots me-1"></i> Community Q&A (<?= count($discussions) ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabBookmarks">
                            <i class="bi bi-bookmark me-1"></i> Bookmarks (<?= count($bookmarks) ?>)
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    
                    <!-- Tab 1: Lesson Guide -->
                    <div class="tab-pane fade show active" id="tabLessonGuide">
                        <?php if (!empty($currentLesson['content'])): ?>
                            <div class="text-dark lh-lg formatted-lesson-content">
                                <?= nl2br(e($currentLesson['content'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">Follow the video instructions above. Additional extraction recipe parameters and guidelines will appear here.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 2: My Timestamped Notes -->
                    <div class="tab-pane fade" id="tabMyNotes">
                        <div class="mb-4 p-3 bg-light rounded-4">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-pencil-fill text-primary me-1"></i> Add Note at Current Time</h6>
                            <form id="formAddNote" class="d-flex flex-column gap-2">
                                <textarea id="noteTextInput" rows="2" class="form-control form-control-sm" placeholder="Write key takeaway, extraction ratio, recipe tip..." required></textarea>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted" id="currentVideoTimeBadge"><i class="bi bi-clock me-1"></i> Time: 00:00</small>
                                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Save Note</button>
                                </div>
                            </form>
                        </div>

                        <div id="notesContainer" class="d-flex flex-column gap-2">
                            <?php if (empty($notes)): ?>
                                <p class="text-muted small py-3 text-center" id="noNotesMsg">No private notes added for this lesson yet.</p>
                            <?php else: ?>
                                <?php foreach ($notes as $n): ?>
                                    <div class="p-3 border rounded-3 bg-white d-flex justify-content-between align-items-start note-item" id="note-item-<?= $n['id'] ?>">
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary border font-monospace mb-1 cursor-pointer btn-jump-timestamp" data-seconds="<?= $n['timestamp_seconds'] ?>">
                                                <i class="bi bi-play-fill"></i> <?= \App\Models\LessonNote::formatTimestamp((int)$n['timestamp_seconds']) ?>
                                            </span>
                                            <p class="text-dark small mb-0"><?= nl2br(e($n['note_text'])) ?></p>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2 btn-delete-note" data-id="<?= $n['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tab 3: Resources & Attachments -->
                    <div class="tab-pane fade" id="tabResources">
                        <?php if (empty($resources)): ?>
                            <p class="text-muted small py-3 text-center">No supplementary attachments or recipe sheets attached to this lesson.</p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($resources as $res): ?>
                                    <div class="col-md-6">
                                        <div class="card border p-3 rounded-4 bg-light d-flex flex-row justify-content-between align-items-center h-100">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="p-2 bg-white rounded-3 shadow-sm text-danger">
                                                    <i class="bi bi-file-earmark-pdf fs-4"></i>
                                                </div>
                                                <div>
                                                    <strong class="d-block text-dark small"><?= e($res['title']) ?></strong>
                                                    <small class="text-muted"><?= \App\Models\LessonResource::formatFileSize($res['file_size']) ?> • <?= $res['download_count'] ?> download(s)</small>
                                                </div>
                                            </div>
                                            <a href="<?= url('student/lesson/resources/' . $res['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 4: Community Q&A Discussions -->
                    <div class="tab-pane fade" id="tabDiscussions">
                        <div class="mb-4 p-3 bg-light rounded-4">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-chat-left-text-fill text-primary me-1"></i> Ask the Instructor or Community</h6>
                            <form id="formPostDiscussion" class="d-flex flex-column gap-2">
                                <textarea id="discussionInput" rows="2" class="form-control form-control-sm" placeholder="Have a question about this step or technique? Ask here..." required></textarea>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Post Question</button>
                                </div>
                            </form>
                        </div>

                        <div id="discussionsContainer" class="d-flex flex-column gap-3">
                            <?php if (empty($discussions)): ?>
                                <p class="text-muted small py-3 text-center" id="noDiscMsg">No questions asked yet for this lesson. Be the first to start the discussion!</p>
                            <?php else: ?>
                                <?php foreach ($discussions as $d): ?>
                                    <div class="p-3 border rounded-4 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="text-dark small"><?= e($d['user_name']) ?></strong>
                                                <?php if (in_array($d['user_role'] ?? '', ['instructor', 'admin', 'super_admin'])): ?>
                                                    <span class="badge bg-warning text-dark" style="font-size:0.65rem;">Instructor</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?= date('M d, Y', strtotime($d['created_at'])) ?></small>
                                        </div>
                                        <p class="text-dark small mb-2"><?= nl2br(e($d['question'])) ?></p>

                                        <!-- Threaded Replies -->
                                        <?php if (!empty($d['replies'])): ?>
                                            <div class="ms-4 mt-2 ps-3 border-start d-flex flex-column gap-2">
                                                <?php foreach ($d['replies'] as $rep): ?>
                                                    <div class="p-2 bg-light rounded-3 small">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <div>
                                                                <strong class="text-dark"><?= e($rep['user_name']) ?></strong>
                                                                <?php if (in_array($rep['user_role'] ?? '', ['instructor', 'admin', 'super_admin'])): ?>
                                                                    <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Verified Instructor</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <span class="text-muted" style="font-size:0.7rem;"><?= date('M d', strtotime($rep['created_at'])) ?></span>
                                                        </div>
                                                        <p class="text-dark mb-0"><?= nl2br(e($rep['question'])) ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tab 5: Bookmarks -->
                    <div class="tab-pane fade" id="tabBookmarks">
                        <div id="bookmarksContainer" class="d-flex flex-column gap-2">
                            <?php if (empty($bookmarks)): ?>
                                <p class="text-muted small py-3 text-center">No bookmarks saved yet. Click "Bookmark Moment" while watching the video to save important moments.</p>
                            <?php else: ?>
                                <?php foreach ($bookmarks as $bm): ?>
                                    <div class="p-3 border rounded-3 bg-white d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-warning text-dark font-monospace cursor-pointer btn-jump-timestamp" data-seconds="<?= $bm['timestamp_seconds'] ?>">
                                                <i class="bi bi-play-fill"></i> <?= \App\Models\LessonNote::formatTimestamp((int)$bm['timestamp_seconds']) ?>
                                            </span>
                                            <strong class="text-dark small"><?= e($bm['title']) ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- Right Collapsible Curriculum Sidebar -->
    <div class="classroom-sidebar">
        <!-- Course Progress Header -->
        <div class="p-3 border-bottom bg-light">
            <h6 class="font-heading mb-1 text-dark fw-bold text-truncate"><?= e($course['title']) ?></h6>
            <div class="d-flex justify-content-between text-muted small mb-1">
                <span>Curriculum Progress</span>
                <span class="fw-bold text-primary" id="sidebarProgressText"><?= e($enrollment['progress_percent'] ?? 0) ?>%</span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-primary" id="sidebarProgressBar" style="width: <?= e($enrollment['progress_percent'] ?? 0) ?>%;"></div>
            </div>

            <!-- Certificate Banner if 100% -->
            <?php if (($enrollment['progress_percent'] ?? 0) >= 100): ?>
                <div class="mt-3 p-2 bg-success-subtle border border-success rounded-3 text-center small">
                    <i class="bi bi-award-fill text-success fs-5 d-block mb-1"></i>
                    <strong class="text-success d-block">Course Completed!</strong>
                    <a href="<?= url('student/certificates') ?>" class="btn btn-sm btn-success fw-bold mt-1 py-0 px-2">View Certificate</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Curriculum Modules Accordion -->
        <div class="classroom-curriculum-list">
            <?php if (empty($modules)): ?>
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-hourglass-split d-block fs-3 mb-2 opacity-50"></i>
                    No modules added yet.
                </div>
            <?php else: ?>
                <?php foreach ($modules as $mod): ?>
                    <div class="p-2 px-3 bg-light border-bottom text-uppercase fw-bold text-muted" style="font-size:0.75rem; letter-spacing:0.5px;">
                        <?= e($mod['title']) ?>
                    </div>

                    <?php if (!empty($mod['lessons'])): ?>
                        <?php foreach ($mod['lessons'] as $les): ?>
                            <?php 
                                $isCurrent = !empty($currentLesson) && ((int)$les['id'] === (int)$currentLesson['id']);
                                $isDone = in_array((int)$les['id'], $completedLessonIds ?? [], true);
                            ?>
                            <a href="<?= url('student/classroom/' . e($course['slug']) . '/' . e($les['id'])) ?>" 
                               class="lesson-item-link <?= $isCurrent ? 'active' : '' ?> d-flex align-items-center gap-2 p-3 border-bottom text-decoration-none"
                               id="lesson-item-<?= $les['id'] ?>">
                                <div class="mt-1">
                                    <i class="bi <?= $isDone ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?> fs-5 lesson-check-icon"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="small d-block text-dark <?= $isCurrent ? 'fw-bold text-primary' : '' ?>"><?= e($les['title']) ?></span>
                                    <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i> <?= e($les['duration_minutes']) ?> min</small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($mod['quizzes'])): ?>
                        <?php foreach ($mod['quizzes'] as $q): ?>
                            <a href="<?= url('student/quiz/' . e($q['id'])) ?>" class="lesson-item-link bg-warning-subtle d-flex align-items-center gap-2 p-3 border-bottom text-decoration-none">
                                <div class="mt-1">
                                    <i class="bi bi-patch-question-fill text-warning fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="small d-block fw-bold text-dark"><?= e($q['title']) ?></span>
                                    <small class="text-muted" style="font-size:0.75rem;">Assessment Exam</small>
                                </div>
                                <span class="badge bg-warning text-dark align-self-center">Quiz</span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const courseId = <?= (int)($course['id'] ?? 0) ?>;
    const enrollmentId = <?= (int)($enrollment['id'] ?? 0) ?>;
    const lessonId = <?= (int)($currentLesson['id'] ?? 0) ?>;
    const initialPosition = <?= (int)($lessonProgress['last_position_seconds'] ?? 0) ?>;
    const btnTheater = document.getElementById('btnToggleTheater');
    const theaterStage = document.getElementById('cinemaTheaterStage');
    const btnComplete = document.getElementById('btnMarkComplete');
    const completeText = document.getElementById('markCompleteText');
    const html5Video = document.getElementById('classroomHtml5Video');
    const timeBadge = document.getElementById('currentVideoTimeBadge');

    // 1. Theater Mode Toggle
    if (btnTheater && theaterStage) {
        btnTheater.addEventListener('click', function () {
            theaterStage.classList.toggle('theater-mode');
        });
    }

    // 2. HTML5 Video Progress Heartbeat & Time Tracking
    let currentTimeSec = initialPosition;
    if (html5Video) {
        if (initialPosition > 0) {
            html5Video.currentTime = initialPosition;
        }

        html5Video.addEventListener('timeupdate', function () {
            currentTimeSec = Math.floor(html5Video.currentTime);
            const m = Math.floor(currentTimeSec / 60).toString().padStart(2, '0');
            const s = (currentTimeSec % 60).toString().padStart(2, '0');
            if (timeBadge) timeBadge.innerHTML = `<i class="bi bi-clock me-1"></i> Time: ${m}:${s}`;
        });

        // Send Heartbeat on Pause
        html5Video.addEventListener('pause', function () {
            sendHeartbeat(currentTimeSec, false);
        });

        // Auto Mark Complete when reached end of video
        html5Video.addEventListener('ended', function () {
            sendHeartbeat(currentTimeSec, true);
        });
    }

    function sendHeartbeat(pos, markComplete) {
        if (!lessonId || !enrollmentId) return;

        fetch('<?= url("student/lesson/progress") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                'enrollment_id': enrollmentId,
                'lesson_id': lessonId,
                'position_seconds': pos,
                'time_spent': 15,
                'mark_complete': markComplete ? 1 : 0,
                'csrf_token': '<?= csrf_token() ?>'
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const pText = document.getElementById('sidebarProgressText');
                const pBar = document.getElementById('sidebarProgressBar');
                if (pText) pText.innerText = data.progress_percent + '%';
                if (pBar) pBar.style.width = data.progress_percent + '%';
            }
        });
    }

    // Periodical Heartbeat every 20 seconds
    setInterval(function () {
        if (html5Video && !html5Video.paused) {
            sendHeartbeat(currentTimeSec, false);
        }
    }, 20000);

    // 3. Mark Lesson Complete Button
    if (btnComplete) {
        btnComplete.addEventListener('click', function () {
            btnComplete.disabled = true;
            fetch('<?= url("student/lesson/complete") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    'enrollment_id': enrollmentId,
                    'lesson_id': lessonId,
                    'csrf_token': '<?= csrf_token() ?>'
                })
            })
            .then(r => r.json())
            .then(data => {
                btnComplete.disabled = false;
                if (data.success) {
                    btnComplete.classList.remove('btn-outline-success');
                    btnComplete.classList.add('btn-success');
                    if (completeText) completeText.innerText = 'Completed';
                    const icon = btnComplete.querySelector('i');
                    if (icon) icon.className = 'bi bi-check-circle-fill me-1';

                    const sidebarItem = document.getElementById('lesson-item-' + lessonId);
                    if (sidebarItem) {
                        const checkIcon = sidebarItem.querySelector('.lesson-check-icon');
                        if (checkIcon) checkIcon.className = 'bi bi-check-circle-fill text-success fs-5 lesson-check-icon';
                    }

                    const nextUrl = btnComplete.getAttribute('data-next-url');
                    if (nextUrl) {
                        setTimeout(() => window.location.href = nextUrl, 800);
                    }
                }
            });
        });
    }

    // 4. Save Private Note Form
    const formNote = document.getElementById('formAddNote');
    if (formNote) {
        formNote.addEventListener('submit', function (e) {
            e.preventDefault();
            const textInput = document.getElementById('noteTextInput');
            const noteText = textInput.value.trim();
            if (!noteText) return;

            fetch('<?= url("student/lesson/notes") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    'course_id': courseId,
                    'lesson_id': lessonId,
                    'note_text': noteText,
                    'timestamp_seconds': currentTimeSec,
                    'csrf_token': '<?= csrf_token() ?>'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    textInput.value = '';
                    const noNotes = document.getElementById('noNotesMsg');
                    if (noNotes) noNotes.remove();

                    const container = document.getElementById('notesContainer');
                    const newNoteHtml = `
                        <div class="p-3 border rounded-3 bg-white d-flex justify-content-between align-items-start note-item" id="note-item-${data.note_id}">
                            <div>
                                <span class="badge bg-primary-subtle text-primary border font-monospace mb-1 cursor-pointer btn-jump-timestamp" data-seconds="${currentTimeSec}">
                                    <i class="bi bi-play-fill"></i> ${data.timestamp_formatted}
                                </span>
                                <p class="text-dark small mb-0">${noteText}</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2 btn-delete-note" data-id="${data.note_id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                    container.insertAdjacentHTML('afterbegin', newNoteHtml);
                }
            });
        });
    }

    // 5. Delete Note Event Delegation
    document.addEventListener('click', function (e) {
        const btnDel = e.target.closest('.btn-delete-note');
        if (btnDel) {
            const noteId = btnDel.getAttribute('data-id');
            if (confirm('Delete this note?')) {
                fetch('<?= url("student/lesson/notes") ?>/' + noteId + '/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ 'csrf_token': '<?= csrf_token() ?>' })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const item = document.getElementById('note-item-' + noteId);
                        if (item) item.remove();
                    }
                });
            }
        }

        // Jump Timestamp
        const btnJump = e.target.closest('.btn-jump-timestamp');
        if (btnJump) {
            const sec = parseInt(btnJump.getAttribute('data-seconds'));
            if (html5Video) {
                html5Video.currentTime = sec;
                html5Video.play();
            }
        }
    });

    // 6. Post Discussion Form
    const formDisc = document.getElementById('formPostDiscussion');
    if (formDisc) {
        formDisc.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = document.getElementById('discussionInput');
            const question = input.value.trim();
            if (!question) return;

            fetch('<?= url("student/lesson/discussions") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    'course_id': courseId,
                    'lesson_id': lessonId,
                    'question': question,
                    'csrf_token': '<?= csrf_token() ?>'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    const noDisc = document.getElementById('noDiscMsg');
                    if (noDisc) noDisc.remove();

                    const container = document.getElementById('discussionsContainer');
                    const newDiscHtml = `
                        <div class="p-3 border rounded-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-dark small"><?= e(auth_user()['name'] ?? 'Me') ?></strong>
                                <small class="text-muted">Just now</small>
                            </div>
                            <p class="text-dark small mb-0">${question}</p>
                        </div>
                    `;
                    container.insertAdjacentHTML('afterbegin', newDiscHtml);
                }
            });
        });
    }

    // 7. Quick Bookmark Button
    const btnBm = document.getElementById('btnQuickBookmark');
    if (btnBm) {
        btnBm.addEventListener('click', function () {
            fetch('<?= url("student/lesson/bookmarks") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    'lesson_id': lessonId,
                    'timestamp_seconds': currentTimeSec,
                    'csrf_token': '<?= csrf_token() ?>'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                }
            });
        });
    }
});
</script>
