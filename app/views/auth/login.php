<h2>Вход</h2>

<?php if (isset($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="index.php?url=auth/login" style="max-width: 400px; margin-top: 20px;">
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
    
    <button type="submit" 
            style="background-color: #333; color: white; padding: 10px 20px; 
                   border: none; cursor: pointer; border-radius: 3px;">
        Вход
    </button>
</form>

<p style="margin-top: 20px;">
    Нямаш акаунт? <a href="index.php?url=auth/register">Регистрирай се тук</a>
</p>

