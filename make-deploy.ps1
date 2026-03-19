Write-Host "Compilando assets..."
npm run build

Write-Host "Gerando zip..."

$source = Get-Location
$output = Join-Path $source "venturize-hotelaria.zip"

$excludeDirs = @(
    ".git", ".claude", "node_modules", "bitz-exports",
    "tests", "printingAgent", "database\seeders",
    "resources\js", "resources\css",
    "storage\logs", "storage\app", "storage\framework",
    "bootstrap\cache"
)

$excludeFiles = @(
    "venturize-hotelaria.zip", "package.json",
    "package-lock.json", "vite.config.js", "make-deploy.ps1", "make-deploy.sh"
)

if (Test-Path $output) { Remove-Item $output }

$files = Get-ChildItem -Path $source -Recurse -File | Where-Object {
    $relativePath = $_.FullName.Substring($source.Path.Length + 1)

    $inExcludedDir = $false
    foreach ($dir in $excludeDirs) {
        if ($relativePath.StartsWith($dir + "\") -or $relativePath -eq $dir) {
            $inExcludedDir = $true
            break
        }
    }

    $isExcludedFile = $excludeFiles -contains $_.Name

    -not $inExcludedDir -and -not $isExcludedFile
}

Compress-Archive -Path $files.FullName -DestinationPath $output -CompressionLevel Optimal

$size = (Get-Item $output).Length / 1MB
Write-Host ("Concluído! Tamanho: {0:N1} MB" -f $size)
