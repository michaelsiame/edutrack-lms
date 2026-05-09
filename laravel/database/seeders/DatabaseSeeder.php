<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            InstructorSeeder::class,
            CourseCategorySeeder::class,
            CourseSeeder::class,
            ModuleSeeder::class,
            LessonSeeder::class,
            QuizSeeder::class,
            EnrollmentSeeder::class,
            PaymentSeeder::class,
            CertificateSeeder::class,
            SystemSettingSeeder::class,
            TestimonialSeeder::class,
            EventSeeder::class,
            HeroSlideSeeder::class,
            InstitutionPhotoSeeder::class,
            // Uncomment below to migrate from legacy database:
            // MigrateLegacyData::class,
        ]);
    }
}
