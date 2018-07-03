<style>
    input{
        width: 100%;
    }
    label{
        margin-bottom: 7px;
        display: block;
    }
</style>
<h1>CMD</h1>
<hr/>
<label for="create_user">Create Admin User</label>
<input name="create_user" type="text" value="php bin/magento admin:user:create --admin-firstname=Dev --admin-lastname=2017 --admin-email=chinhdays@gmail.com --admin-user=admin --admin-password='Admin$123'">
<hr/>