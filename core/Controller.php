<?php

class Controller
{
    protected function render(string $view, array $data = [])
    {
        extract($data);
        
        $user = current_user();
        $verified = $user ? (bool)$user['is_verified'] : false;
        $role = user_role();
        $savedCount = $user && $role === 'user' ? Wishlist::count((int)$user['id']) : 0;
        
        require APP_ROOT . '/app/Views/layouts/header.php';
        require APP_ROOT . '/app/Views/' . $view . '.php';
        require APP_ROOT . '/app/Views/layouts/footer.php';
    }
}
