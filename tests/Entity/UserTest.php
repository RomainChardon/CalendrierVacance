<?php 
use App\Entity\User;
use App\Entity\Vacances;
use PHPUnit\Framework\TestCase;
/** @test */
class UserTest extends TestCase
{
    public function test_default_role_user()
    {
        $user = new User();

        $rolesUser = $user->getRoles();

        $checkRoleUser = array_search("ROLE_USER",$rolesUser);
        $checkRoleAdmin = array_search("ROLE_ADMIN",$rolesUser);

        $this->assertIsNotBool($checkRoleUser);
        $this->assertFalse($checkRoleAdmin);
    }
}