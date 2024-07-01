<?php

namespace App\Controllers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\DownloadsService;

class DownloadsController extends AbstractController
{
    
    #[Route('/api/downloads/monthlyDownloadReset', name: 'app_download_monthly_reset', methods: ['POST'])]
    public function monthlyReset(DownloadsService $downloadsService): JsonResponse
    {
       return $downloadsService->resetAllUsersDownloadLimit();  
    }
}