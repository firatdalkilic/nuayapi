                                    <div class="col-md-6">
                                        <p><strong>İlan Tipi:</strong> <?php echo $property['property_type']; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="feature-item">
                                          <i class="bi bi-building-fill"></i>
                                          <span><strong>Bina Yaşı:</strong> <?php 
                                          $building_age = $property['building_age'];
                                          echo '<span style="background-color: #fff3cd; padding: 2px 5px; margin-left: 5px; border-radius: 3px;">';
                                          echo 'Debug: ' . var_export($building_age, true);
                                          echo '</span><br>';
                                          
                                          if ($building_age !== null && $building_age !== '') {
                                              if ($building_age === '0' || $building_age === 0) {
                                                  echo '0 (Yeni)';
                                              } elseif ($building_age == '11' || ($building_age >= 11 && $building_age <= 15)) {
                                                  echo '11-15';
                                              } elseif ($building_age == '16' || ($building_age >= 16 && $building_age <= 20)) {
                                                  echo '16-20';
                                              } elseif ($building_age == '21' || ($building_age >= 21 && $building_age <= 25)) {
                                                  echo '21-25';
                                              } elseif ($building_age == '26' || $building_age >= 26) {
                                                  echo '26+';
                                              } else {
                                                  echo htmlspecialchars($building_age);
                                              }
                                          } else {
                                              echo 'Belirtilmemiş';
                                          }
                                          ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3" 