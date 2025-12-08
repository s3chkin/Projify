<h2>Регистрация</h2>

<?php if (isset($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="index.php?url=auth/register" style="max-width: 400px; margin-top: 20px;">
    <div style="margin-bottom: 15px;">
        <label for="first_name">Име:</label><br>
        <input type="text" id="first_name" name="first_name" required 
               style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="last_name">Фамилия:</label><br>
        <input type="text" id="last_name" name="last_name" required 
               style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required 
               style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="password">Парола:</label><br>
        <input type="password" id="password" name="password" required 
               style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="confirm_password">Потвърди парола:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required 
               style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    
    <button type="submit" 
            style="background-color: #333; color: white; padding: 10px 20px; 
                   border: none; cursor: pointer; border-radius: 3px;">
        Регистрирай се
    </button>
</form>

<p style="margin-top: 20px;">
    Вече имаш акаунт? <a href="index.php?url=auth/login">Влез тук</a>
</p>

