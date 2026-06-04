Write-Host "Compilando assets..."
npm run build

Write-Host "Gerando zip..."

$source = (Get-Location).Path
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
    "package-lock.json", "vite.config.js", "make-deploy.ps1", "make-deploy.sh",
    ".mcp.json"
)

if (Test-Path $output) { Remove-Item $output }

$files = Get-ChildItem -Path $source -Recurse -File | Where-Object {
    $relativePath = $_.FullName.Substring($source.Length + 1)

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

# Build zip preserving relative directory structure
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.IO.Compression

$zip = [System.IO.Compression.ZipFile]::Open($output, [System.IO.Compression.ZipArchiveMode]::Create)
foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($source.Length + 1)
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $file.FullName, $relativePath, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
}
$zip.Dispose()

$size = (Get-Item $output).Length / 1MB
Write-Host ("Concluido! Tamanho: {0:N1} MB" -f $size)
