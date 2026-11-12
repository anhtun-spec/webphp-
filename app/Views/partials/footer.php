<?php declare(strict_types=1); ?>
<footer class="site-footer text-white pt-5 pb-4 mt-5" data-bs-theme="dark">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & Intro -->
            <div class="col-lg-4 col-md-6">
                <a href="<?= url('/') ?>" class="d-inline-flex align-items-center text-white text-decoration-none mb-3">
                    <span class="footer-brand-icon rounded-3 d-inline-flex align-items-center justify-content-center me-2">
                        <i class="bi bi-journal-bookmark-fill fs-5"></i>
                    </span>
                    <span class="fs-5 fw-bold">TMU Tài Liệu</span>
                </a>
                <p class="text-white-50 small lh-lg mb-3">
                    Kho học liệu số chia sẻ giáo trình, slide bài giảng, đề thi và tài liệu ôn tập dành riêng cho cộng đồng sinh viên Trường Đại học Thương mại.
                </p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-white bg-opacity-10 text-light border border-light border-opacity-10 fw-normal">PDF</span>
                    <span class="badge bg-white bg-opacity-10 text-light border border-light border-opacity-10 fw-normal">DOC / DOCX</span>
                    <span class="badge bg-white bg-opacity-10 text-light border border-light border-opacity-10 fw-normal">PPT / PPTX</span>
                    <span class="badge bg-white bg-opacity-10 text-light border border-light border-opacity-10 fw-normal">XLS / XLSX</span>
                    <span class="badge bg-white bg-opacity-10 text-light border border-light border-opacity-10 fw-normal">ZIP / RAR</span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-heading text-uppercase fw-bold mb-3">Khám phá</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li>
                        <a href="<?= url('/') ?>" class="footer-link">
                            <i class="bi bi-chevron-right me-1 small"></i>Trang chủ
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/documents') ?>" class="footer-link">
                            <i class="bi bi-chevron-right me-1 small"></i>Tất cả tài liệu
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/documents?sort=downloads') ?>" class="footer-link">
                            <i class="bi bi-chevron-right me-1 small"></i>Tải nhiều nhất
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/faculties') ?>" class="footer-link">
                            <i class="bi bi-chevron-right me-1 small"></i>Khoa / Viện
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/search') ?>" class="footer-link">
                            <i class="bi bi-chevron-right me-1 small"></i>Tìm kiếm nâng cao
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Student / Account Links -->
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading text-uppercase fw-bold mb-3">Sinh viên &amp; Tài khoản</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li>
                        <a href="<?= url('/documents/create') ?>" class="footer-link">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Đóng góp tài liệu
                        </a>
                    </li>
                    <?php if (auth()): ?>
                        <li>
                            <a href="<?= url('/my-documents') ?>" class="footer-link">
                                <i class="bi bi-folder2-open me-2"></i>Tài liệu của tôi
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('/favorites') ?>" class="footer-link">
                                <i class="bi bi-bookmark-heart me-2"></i>Tài liệu đã lưu
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('/history') ?>" class="footer-link">
                                <i class="bi bi-clock-history me-2"></i>Lịch sử tải xuống
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('/profile') ?>" class="footer-link">
                                <i class="bi bi-person-circle me-2"></i>Hồ sơ cá nhân
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?= url('/login') ?>" class="footer-link">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('/register') ?>" class="footer-link">
                                <i class="bi bi-person-plus me-2"></i>Đăng ký tài khoản
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('/forgot-password') ?>" class="footer-link">
                                <i class="bi bi-key me-2"></i>Quên mật khẩu
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- University Contact Info -->
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading text-uppercase fw-bold mb-3">Trường Đại học Thương mại</h6>
                <ul class="list-unstyled small text-white-50 d-flex flex-column gap-2 mb-0">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill text-warning mt-1"></i>
                        <span><strong class="text-white">Cơ sở 1:</strong> 79 Hồ Tùng Mậu, Mai Dịch, Cầu Giấy, Hà Nội</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt text-warning mt-1"></i>
                        <span><strong class="text-white">Cơ sở 2:</strong> Lý Thường Kiệt, Lê Hồng Phong, Phủ Lý, Hà Nam</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-globe text-info"></i>
                        <a href="https://tmu.edu.vn" class="footer-link" target="_blank" rel="noopener noreferrer">
                            tmu.edu.vn <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                        </a>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope text-info"></i>
                        <a href="mailto:mail@tmu.edu.vn" class="footer-link">mail@tmu.edu.vn</a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-light border-opacity-10 my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-white-50">
            <p class="mb-0 text-center text-md-start">
                &copy; <?= date('Y') ?> <strong class="text-white">TMU Tài Liệu</strong>. Dự án học tập — Không phải cổng thông tin chính thức của Trường Đại học Thương mại.
            </p>
            <a href="#top" class="footer-link d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-up-circle"></i>Lên đầu trang
            </a>
        </div>
    </div>
</footer>
