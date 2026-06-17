<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Runs every course content + diagram + cleanup seeder for deployment.
 * All child seeders are guarded/idempotent (build seeders skip courses that
 * already have modules; diagram seeders skip lessons already carrying the
 * figure), so this is safe to run repeatedly on production.
 */
class ContentEnrichmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \Database\Seeders\Content\ArtificialIntelligenceContentSeeder::class,
            \Database\Seeders\Content\ComputerAndBusinessHandlingContentSeeder::class,
            \Database\Seeders\Content\ComputerScienceGeneralContentSeeder::class,
            \Database\Seeders\Content\ComputerStudiesContentSeeder::class,
            \Database\Seeders\Content\CppProgrammingContentSeeder::class,
            \Database\Seeders\Content\CyberSecurityContentSeeder::class,
            \Database\Seeders\Content\DataAnalysisContentSeeder::class,
            \Database\Seeders\Content\DatabaseManagementSystemsContentSeeder::class,
            \Database\Seeders\Content\DigitalContentCreationContentSeeder::class,
            \Database\Seeders\Content\DigitalLiteracyContentSeeder::class,
            \Database\Seeders\Content\DigitalMarketingContentSeeder::class,
            \Database\Seeders\Content\ECommerceOnlineBusinessContentSeeder::class,
            \Database\Seeders\Content\EntrepreneurshipContentSeeder::class,
            \Database\Seeders\Content\FillOfficeLessonsContentSeeder::class,
            \Database\Seeders\Content\FillProgrammingLessonsContentSeeder::class,
            \Database\Seeders\Content\FinancialTechnologyContentSeeder::class,
            \Database\Seeders\Content\GraphicDesigningContentSeeder::class,
            \Database\Seeders\Content\IctSupportHardwareRepairContentSeeder::class,
            \Database\Seeders\Content\InformationTechnologyContentSeeder::class,
            \Database\Seeders\Content\IoTContentSeeder::class,
            \Database\Seeders\Content\JavaProgrammingContentSeeder::class,
            \Database\Seeders\Content\MicrosoftOfficeSuiteContentSeeder::class,
            \Database\Seeders\Content\MonitoringAndEvaluationContentSeeder::class,
            \Database\Seeders\Content\ProjectManagementContentSeeder::class,
            \Database\Seeders\Content\PurchasingAndSupplyContentSeeder::class,
            \Database\Seeders\Content\PythonProgrammingContentSeeder::class,
            \Database\Seeders\Content\RecordManagementContentSeeder::class,
            \Database\Seeders\Content\SalesAndMarketingContentSeeder::class,
            \Database\Seeders\Content\SecretarialOfficeManagementContentSeeder::class,
            \Database\Seeders\Content\SoftwareEngineeringContentSeeder::class,
            \Database\Seeders\Content\WebDevelopmentContentSeeder::class,
            \Database\Seeders\Content\AiMachineLearningDiagramsSeeder::class,
            \Database\Seeders\Content\ComputerBusinessHandlingDiagramsSeeder::class,
            \Database\Seeders\Content\ComputerScienceGeneralDiagramsSeeder::class,
            \Database\Seeders\Content\ComputerStudiesDiagramsSeeder::class,
            \Database\Seeders\Content\CppProgrammingDiagramsSeeder::class,
            \Database\Seeders\Content\CyberSecurityDiagramsSeeder::class,
            \Database\Seeders\Content\CybersecurityFundamentalsDiagramsSeeder::class,
            \Database\Seeders\Content\DataAnalysisDiagramsSeeder::class,
            \Database\Seeders\Content\DatabaseManagementDiagramsSeeder::class,
            \Database\Seeders\Content\DigitalContentCreationDiagramsSeeder::class,
            \Database\Seeders\Content\DigitalLiteracyDiagramsSeeder::class,
            \Database\Seeders\Content\DigitalMarketingDiagramsSeeder::class,
            \Database\Seeders\Content\EntrepreneurshipDiagramsSeeder::class,
            \Database\Seeders\Content\FinancialTechnologyDiagramsSeeder::class,
            \Database\Seeders\Content\GraphicDesigningDiagramsSeeder::class,
            \Database\Seeders\Content\IctSupportHardwareRepairDiagramsSeeder::class,
            \Database\Seeders\Content\InformationTechnologyDiagramsSeeder::class,
            \Database\Seeders\Content\JavaProgrammingDiagramsSeeder::class,
            \Database\Seeders\Content\MicrosoftOfficeSuiteDiagramsSeeder::class,
            \Database\Seeders\Content\MobileAppDevelopmentDiagramsSeeder::class,
            \Database\Seeders\Content\ProjectManagementDiagramsSeeder::class,
            \Database\Seeders\Content\PythonProgrammingDiagramsSeeder::class,
            \Database\Seeders\Content\RecordManagementDiagramsSeeder::class,
            \Database\Seeders\Content\SoftwareEngineeringGitDiagramsSeeder::class,
            \Database\Seeders\Content\WebDevelopmentDiagramsSeeder::class,
            \Database\Seeders\Content\MobileAppOrphanQuizCleanupSeeder::class,
        ]);
    }
}
