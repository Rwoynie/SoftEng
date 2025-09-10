<div class="login-container">
    <h2>Welcome!</h2>
    <p>Access and upload research papers using your official USeP account.</p>
    
    <form action="<?php echo URLROOT; ?>/auth/login" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo $data['email']; ?>" required>
            <span class="error"><?php echo $data['email_err']; ?></span>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" required>
            <span class="error"><?php echo $data['password_err']; ?></span>
        </div>
        
        <button type="submit" class="btn-login">Login</button>
    </form>
    
    <div class="back">
        <a href="<?php echo URLROOT; ?>/public/index"><i class="fa-solid fa-house"></i> Back to Home</a>
    </div>
</div>