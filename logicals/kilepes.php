<?php
app_logout_session();
app_flash('success', 'Sikeres kilépés.');
app_redirect('/');
