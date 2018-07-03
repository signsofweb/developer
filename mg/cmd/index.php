<style>
    input{
        width: 100%;
    }
    label{
        margin-top:10px;
        margin-bottom: 7px;
        display: block;
    }
</style>
<h1>CMD</h1>
<hr/>
<label for="">Set Mode Developer</label>
<input type="text" value="php bin/magento deploy:mode:set developer">
<hr/>
<label for="">Compile</label>
<input type="text" value="php bin/magento setup:di:compile">
<hr/>
<label for="create_user">Create Admin User</label>
<input name="create_user" type="text" value="php bin/magento admin:user:create --admin-firstname=Dev --admin-lastname=2017 --admin-email=chinhdays@gmail.com --admin-user=admin --admin-password='Admin$123'">
<hr/>
<label for="">Public Key</label>
<input type="text" value="25939b261355bd2959d4351b61a2ed97">
<label for="">Private Key</label>
<input type="text" value="b1321212d26906b615db8f979f18b4f4">
<hr/>
