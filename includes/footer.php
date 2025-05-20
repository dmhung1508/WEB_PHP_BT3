</main>
    
    <!-- Footer -->
    <footer class="bg-white py-5 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3">DIENTHOAIRE</h5>
                    <p class="text-muted">Cung cấp các sản phẩm công nghệ chất lượng cao với giá cả hợp lý.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-dark"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-dark"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-dark"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-3">Sản phẩm</h6>
                    <ul class="list-unstyled">
                        <?php
                        $categories = get_categories();
                        foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li class="mb-2">
                                <a href="category.php?id=<?php echo $category['id']; ?>" class="text-decoration-none text-muted">
                                    <?php echo $category['name']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-3">Thông tin</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Về chúng tôi</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Chính sách bảo mật</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Điều khoản sử dụng</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Chính sách đổi trả</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Liên hệ</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> 123 Đường ABC, Quận XYZ, TP. HCM</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> (84) 123 456 789</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> info@DIENTHOAIRE.com</li>
                    </ul>
                    <form class="mt-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Đăng ký nhận tin">
                            <button class="btn btn-dark" type="button">Đăng ký</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
            <div class="text-center text-muted">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> DIENTHOAIRE. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
