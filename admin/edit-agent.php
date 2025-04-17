                    <div class="mb-3">
                        <label for="sahibinden_store" class="form-label">Sahibinden.com Mağaza Adı</label>
                        <input type="text" class="form-control" id="sahibinden_store" name="sahibinden_store" value="<?php echo htmlspecialchars($agent['sahibinden_store'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="emlakjet_profile" class="form-label">Emlakjet Profil Linki</label>
                        <input type="text" class="form-control" id="emlakjet_profile" name="emlakjet_profile" value="<?php echo htmlspecialchars($agent['emlakjet_profile'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="facebook_username" class="form-label">Facebook Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text">facebook.com/</span>
                            <input type="text" class="form-control" id="facebook_username" name="facebook_username" value="<?php echo htmlspecialchars($agent['facebook_username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="instagram_username" class="form-label">Instagram Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text">instagram.com/</span>
                            <input type="text" class="form-control" id="instagram_username" name="instagram_username" value="<?php echo htmlspecialchars($agent['instagram_username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="twitter_username" class="form-label">Twitter Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text">twitter.com/</span>
                            <input type="text" class="form-control" id="twitter_username" name="twitter_username" value="<?php echo htmlspecialchars($agent['twitter_username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="linkedin_username" class="form-label">LinkedIn Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text">linkedin.com/in/</span>
                            <input type="text" class="form-control" id="linkedin_username" name="linkedin_username" value="<?php echo htmlspecialchars($agent['linkedin_username'] ?? ''); ?>">
                        </div>
                    </div> 