<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class GzipResponse
{
    public function handle(Request $request, Closure $next)
    { 
     
        $response = $next($request);
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
        return $response; 
    }
 
        $excludedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/zip',
            'application/x-rar-compressed',
            'application/pdf',
            'application/octet-stream',
        ];
    
        $contentType = $response->headers->get('Content-Type');
    
        if (!str_contains($request->header('Accept-Encoding'), 'gzip') || $this->isExcluded($contentType, $excludedTypes)) {
            return $response;
        }
    
        $content = $response->getContent();
        $compressed = gzencode($content, 9); 
    
        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', strlen($compressed));
    $response->headers->set('X-Uncompressed-Length', strlen($content));
        return $response;
    }
    
    private function isExcluded(?string $type, array $excluded): bool
    {
        foreach ($excluded as $excludedType) {
            if (str_starts_with($type, $excludedType)) {
                return true;
            }
        }
        return false;
    }
    
}
