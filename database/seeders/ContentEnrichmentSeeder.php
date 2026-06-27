<?php

namespace Database\Seeders;

use Database\Seeders\Content\AiMachineLearningDiagramsSeeder;
use Database\Seeders\Content\ArtificialIntelligenceContentSeeder;
use Database\Seeders\Content\ComputerAndBusinessHandlingContentSeeder;
use Database\Seeders\Content\ComputerBusinessHandlingDiagramsSeeder;
use Database\Seeders\Content\ComputerScienceGeneralContentSeeder;
use Database\Seeders\Content\ComputerScienceGeneralDiagramsSeeder;
use Database\Seeders\Content\ComputerStudiesContentSeeder;
use Database\Seeders\Content\ComputerStudiesDiagramsSeeder;
use Database\Seeders\Content\CppProgrammingContentSeeder;
use Database\Seeders\Content\CppProgrammingDiagramsSeeder;
use Database\Seeders\Content\CyberSecurityContentSeeder;
use Database\Seeders\Content\CyberSecurityDiagramsSeeder;
use Database\Seeders\Content\CybersecurityFundamentalsDiagramsSeeder;
use Database\Seeders\Content\DataAnalysisContentSeeder;
use Database\Seeders\Content\DataAnalysisDiagramsSeeder;
use Database\Seeders\Content\DatabaseManagementDiagramsSeeder;
use Database\Seeders\Content\DatabaseManagementSystemsContentSeeder;
use Database\Seeders\Content\DigitalContentCreationContentSeeder;
use Database\Seeders\Content\DigitalContentCreationDiagramsSeeder;
use Database\Seeders\Content\DigitalLiteracyContentSeeder;
use Database\Seeders\Content\DigitalLiteracyDiagramsSeeder;
use Database\Seeders\Content\DigitalMarketingContentSeeder;
use Database\Seeders\Content\DigitalMarketingDiagramsSeeder;
use Database\Seeders\Content\ECommerceOnlineBusinessContentSeeder;
use Database\Seeders\Content\EntrepreneurshipContentSeeder;
use Database\Seeders\Content\EntrepreneurshipDiagramsSeeder;
use Database\Seeders\Content\FillOfficeLessonsContentSeeder;
use Database\Seeders\Content\FillProgrammingLessonsContentSeeder;
use Database\Seeders\Content\FinancialTechnologyContentSeeder;
use Database\Seeders\Content\FinancialTechnologyDiagramsSeeder;
use Database\Seeders\Content\GraphicDesigningContentSeeder;
use Database\Seeders\Content\GraphicDesigningDiagramsSeeder;
use Database\Seeders\Content\IctSupportHardwareRepairContentSeeder;
use Database\Seeders\Content\IctSupportHardwareRepairDiagramsSeeder;
use Database\Seeders\Content\InformationTechnologyContentSeeder;
use Database\Seeders\Content\InformationTechnologyDiagramsSeeder;
use Database\Seeders\Content\IoTContentSeeder;
use Database\Seeders\Content\JavaProgrammingContentSeeder;
use Database\Seeders\Content\JavaProgrammingDiagramsSeeder;
use Database\Seeders\Content\MicrosoftOfficeSuiteContentSeeder;
use Database\Seeders\Content\MicrosoftOfficeSuiteDiagramsSeeder;
use Database\Seeders\Content\MobileAppDevelopmentDiagramsSeeder;
use Database\Seeders\Content\MobileAppOrphanQuizCleanupSeeder;
use Database\Seeders\Content\MonitoringAndEvaluationContentSeeder;
use Database\Seeders\Content\ProjectManagementContentSeeder;
use Database\Seeders\Content\ProjectManagementDiagramsSeeder;
use Database\Seeders\Content\PurchasingAndSupplyContentSeeder;
use Database\Seeders\Content\PythonProgrammingContentSeeder;
use Database\Seeders\Content\PythonProgrammingDiagramsSeeder;
use Database\Seeders\Content\RecordManagementContentSeeder;
use Database\Seeders\Content\RecordManagementDiagramsSeeder;
use Database\Seeders\Content\SalesAndMarketingContentSeeder;
use Database\Seeders\Content\SecretarialOfficeManagementContentSeeder;
use Database\Seeders\Content\SoftwareEngineeringContentSeeder;
use Database\Seeders\Content\SoftwareEngineeringGitDiagramsSeeder;
use Database\Seeders\Content\TradeCertificateComputerStudiesLevel3ContentSeeder;
use Database\Seeders\Content\TradeCertificateComputerStudiesLevel3DiagramsSeeder;
use Database\Seeders\Content\WebDevelopmentContentSeeder;
use Database\Seeders\Content\WebDevelopmentDiagramsSeeder;
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
            ArtificialIntelligenceContentSeeder::class,
            ComputerAndBusinessHandlingContentSeeder::class,
            ComputerScienceGeneralContentSeeder::class,
            ComputerStudiesContentSeeder::class,
            CppProgrammingContentSeeder::class,
            CyberSecurityContentSeeder::class,
            DataAnalysisContentSeeder::class,
            DatabaseManagementSystemsContentSeeder::class,
            DigitalContentCreationContentSeeder::class,
            DigitalLiteracyContentSeeder::class,
            DigitalMarketingContentSeeder::class,
            ECommerceOnlineBusinessContentSeeder::class,
            EntrepreneurshipContentSeeder::class,
            FillOfficeLessonsContentSeeder::class,
            FillProgrammingLessonsContentSeeder::class,
            FinancialTechnologyContentSeeder::class,
            GraphicDesigningContentSeeder::class,
            IctSupportHardwareRepairContentSeeder::class,
            InformationTechnologyContentSeeder::class,
            IoTContentSeeder::class,
            JavaProgrammingContentSeeder::class,
            MicrosoftOfficeSuiteContentSeeder::class,
            MonitoringAndEvaluationContentSeeder::class,
            ProjectManagementContentSeeder::class,
            PurchasingAndSupplyContentSeeder::class,
            PythonProgrammingContentSeeder::class,
            RecordManagementContentSeeder::class,
            SalesAndMarketingContentSeeder::class,
            SecretarialOfficeManagementContentSeeder::class,
            SoftwareEngineeringContentSeeder::class,
            TradeCertificateComputerStudiesLevel3ContentSeeder::class,
            WebDevelopmentContentSeeder::class,
            AiMachineLearningDiagramsSeeder::class,
            ComputerBusinessHandlingDiagramsSeeder::class,
            ComputerScienceGeneralDiagramsSeeder::class,
            ComputerStudiesDiagramsSeeder::class,
            CppProgrammingDiagramsSeeder::class,
            CyberSecurityDiagramsSeeder::class,
            CybersecurityFundamentalsDiagramsSeeder::class,
            DataAnalysisDiagramsSeeder::class,
            DatabaseManagementDiagramsSeeder::class,
            DigitalContentCreationDiagramsSeeder::class,
            DigitalLiteracyDiagramsSeeder::class,
            DigitalMarketingDiagramsSeeder::class,
            EntrepreneurshipDiagramsSeeder::class,
            FinancialTechnologyDiagramsSeeder::class,
            GraphicDesigningDiagramsSeeder::class,
            IctSupportHardwareRepairDiagramsSeeder::class,
            InformationTechnologyDiagramsSeeder::class,
            JavaProgrammingDiagramsSeeder::class,
            MicrosoftOfficeSuiteDiagramsSeeder::class,
            MobileAppDevelopmentDiagramsSeeder::class,
            ProjectManagementDiagramsSeeder::class,
            PythonProgrammingDiagramsSeeder::class,
            RecordManagementDiagramsSeeder::class,
            SoftwareEngineeringGitDiagramsSeeder::class,
            TradeCertificateComputerStudiesLevel3DiagramsSeeder::class,
            WebDevelopmentDiagramsSeeder::class,
            MobileAppOrphanQuizCleanupSeeder::class,
        ]);
    }
}
