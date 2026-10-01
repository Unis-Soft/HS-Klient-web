param(
    [string]$ExePath = (Join-Path $PSScriptRoot "HSBridge.exe")
)

$ErrorActionPreference = "Stop"

$thumbprint = "3A24411F6A75B66380FA0085AA09F7536FCFB014"
$timestampUrl = "http://timestamp.sectigo.com"

function Find-SignTool {
    $cmd = Get-Command signtool.exe -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }

    $kits = Join-Path $env:ProgramFiles(x86) "Windows Kits\10\bin"
    if (Test-Path $kits) {
        $candidate = Get-ChildItem -Path $kits -Directory -ErrorAction SilentlyContinue |
            Sort-Object Name -Descending |
            ForEach-Object { Join-Path $_.FullName "x64\signtool.exe" } |
            Where-Object { Test-Path $_ } |
            Select-Object -First 1
        if ($candidate) { return $candidate }
    }
    throw "signtool.exe nebyl nalezen. Nainstalujte Windows SDK / SignTool."
}

if (!(Test-Path $ExePath)) {
    throw "Soubor nebyl nalezen: $ExePath"
}

$cert = Get-ChildItem Cert:\CurrentUser\My, Cert:\LocalMachine\My -ErrorAction SilentlyContinue |
    Where-Object { ($_.Thumbprint -replace " ","").ToUpperInvariant() -eq $thumbprint } |
    Select-Object -First 1

if (!$cert) {
    throw "Certifikat UnisSoft s.r.o. s thumbprintem $thumbprint neni ve Windows certificate store dostupny. Vlozte YubiKey a zkontrolujte middleware/certifikat."
}

$signtool = Find-SignTool
Write-Host "SignTool: $signtool"
Write-Host "Certifikat: $($cert.Subject)"
Write-Host "Podepisuji: $ExePath"

$backup = [System.IO.Path]::ChangeExtension($ExePath, ".unsigned.exe")
if (!(Test-Path $backup)) {
    Copy-Item -LiteralPath $ExePath -Destination $backup
}

& $signtool sign /v /sha1 $thumbprint /n "UnisSoft s.r.o." /fd SHA256 /tr $timestampUrl /td SHA256 $ExePath
if ($LASTEXITCODE -ne 0) {
    throw "Podepisovani selhalo. SignTool exit code: $LASTEXITCODE"
}

& $signtool verify /pa /v $ExePath
if ($LASTEXITCODE -ne 0) {
    throw "Overeni podpisu selhalo. SignTool exit code: $LASTEXITCODE"
}

Write-Host ""
Write-Host "HOTOVO: HSBridge.exe je podepsany a podpis byl overen."
Write-Host "Zaloha puvodniho nepodepsaneho EXE: $backup"
