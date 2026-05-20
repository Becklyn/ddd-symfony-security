2.0.0
=====

* (improvement) Add support for Symfony 7 (`^6.4 || ^7.0` constraints on all `symfony/*` deps).
* (improvement) Bump `becklyn/ddd-symfony-bridge` requirement to `^5.0`.
* (improvement) Add new `SymfonyCreateUser` application service using `UserPasswordHasherInterface`.
* (breaking) `SymfonySecurity` no longer depends on the removed `Symfony\Component\Security\Core\Security`; it now depends on `TokenStorageInterface` directly.
* (breaking) `ResetPasswordForUser::execute()` signature restored to accept `ResetPassword $command` as third argument (correlates domain events with the triggering command).
* (breaking) Domain services `ChangePasswordForUser::execute()` and `RequestPasswordResetForUser::execute()` require their respective application command as the last argument (already introduced in 1.0.5; documented here for clarity).
* (internal) Replace deprecated `Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface` (removed in SF6) with `Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface` in all infrastructure services and tests.

1.0.5
=====

* (bugfix) Fix all the rest of the Application Commands for newer Versions of Becklyn\DDD Bundle.

1.0.4
=====

* (bugfix) Fix ChangePassword for newer Versions of Becklyn\DDD Bundle.

1.0.3
=====

* (improvement) Update Dependencies.


1.0.2
=====

* (bugfix) Deleted old Doctrine XML Mapping Drivers. Fixed Entity Directories for Annotation Mapping.


1.0.1
=====

* (improvement) Fix Deprecations from Symfony 6 and PHP Upgrade.


1.0.0
=====

*   (improvement) Added Support for PHP 8.1 and Symfony 6.
