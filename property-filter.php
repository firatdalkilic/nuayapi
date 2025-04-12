                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="building_age" class="form-label">Bina Yaşı</label>
                                    <select class="form-select" id="building_age" name="building_age">
                                        <option value="">Tümü</option>
                                        <option value="0" <?php echo isset($_GET['building_age']) && $_GET['building_age'] == '0' ? 'selected' : ''; ?>>0 (Yeni)</option>
                                        <option value="1" <?php echo isset($_GET['building_age']) && $_GET['building_age'] == '1' ? 'selected' : ''; ?>>1-5</option>
                                        <option value="6" <?php echo isset($_GET['building_age']) && $_GET['building_age'] == '6' ? 'selected' : ''; ?>>6-10</option>
                                        <option value="11" <?php echo isset($_GET['building_age']) && $_GET['building_age'] == '11' ? 'selected' : ''; ?>>11-15</option>
                                        <option value="16" <?php echo isset($_GET['building_age']) && $_GET['building_age'] == '16' ? 'selected' : ''; ?>>16-20</option>
                                        <option value="21" <?php echo isset($_GET['building_age']) && $_GET['building_age'] == '21' ? 'selected' : ''; ?>>20+</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
// ... existing code ...

// Filtreleme sorgusuna building_age ekleyelim
if (isset($_GET['building_age']) && $_GET['building_age'] !== '') {
    $conditions[] = "building_age = ?";
    $params[] = $_GET['building_age'];
    $types .= "s";
} 