from __future__ import annotations
import struct
import sys
from pathlib import Path

VERSION = "V010"
FILE_VERSION = "0.0.10.0"

REPLACEMENTS = {
    "HairSoft Voucher Bridge V016": "HSBridge V010",
    "0.0.16.0": FILE_VERSION,
    "HSVoucherBridge": "HSBridge",
    "HSVoucherBridge.exe": "HSBridge.exe",
    "HairSoft Voucher Bridge": "HSBridge",
}

def replace_utf16_fixed(data: bytearray, old: str, new: str) -> int:
    old_b = (old + "\0").encode("utf-16le")
    new_b = (new + "\0").encode("utf-16le")
    if len(new_b) > len(old_b):
        raise RuntimeError(f"Replacement too long: {new!r} > {old!r}")
    padded = new_b + b"\0" * (len(old_b) - len(new_b))
    count = 0
    start = 0
    while True:
        pos = data.find(old_b, start)
        if pos < 0:
            break
        data[pos:pos + len(old_b)] = padded
        count += 1
        start = pos + len(old_b)
    return count

def patch_fixed_version(data: bytearray) -> None:
    sig = b"\xbd\x04\xef\xfe"
    positions = []
    start = 0
    while True:
        pos = data.find(sig, start)
        if pos < 0:
            break
        positions.append(pos)
        start = pos + 1
    if len(positions) != 1:
        raise RuntimeError(f"Expected exactly one VS_FIXEDFILEINFO, found {len(positions)}")
    pos = positions[0]
    # VS_FIXEDFILEINFO:
    # signature, structVersion, fileVersionMS, fileVersionLS,
    # productVersionMS, productVersionLS, ...
    struct.pack_into("<I", data, pos + 8, 0x00000000)
    struct.pack_into("<I", data, pos + 12, 0x000A0000)
    struct.pack_into("<I", data, pos + 16, 0x00000000)
    struct.pack_into("<I", data, pos + 20, 0x00010000)

def main() -> int:
    if len(sys.argv) != 2:
        print("usage: patch_version.py <exe>", file=sys.stderr)
        return 2
    path = Path(sys.argv[1])
    data = bytearray(path.read_bytes())

    patch_fixed_version(data)
    counts = {}
    for old, new in REPLACEMENTS.items():
        counts[old] = replace_utf16_fixed(data, old, new)

    required = [
        "HairSoft Voucher Bridge V016",
        "HSVoucherBridge",
        "HSVoucherBridge.exe",
        "HairSoft Voucher Bridge",
    ]
    for key in required:
        if counts.get(key, 0) < 1:
            raise RuntimeError(f"Version resource field not found: {key}")

    # 0.0.16.0 occurs in FileVersion and ProductVersion.
    # First replacement makes both 0.0.10.0; ProductVersion is then changed
    # to V010 by locating the ProductVersion value specifically.
    product_key = ("ProductVersion\0").encode("utf-16le")
    key_pos = data.find(product_key)
    if key_pos < 0:
        raise RuntimeError("ProductVersion key not found")
    numeric = (FILE_VERSION + "\0").encode("utf-16le")
    value_pos = data.find(numeric, key_pos + len(product_key))
    if value_pos < 0:
        raise RuntimeError("ProductVersion value not found")
    replacement = (VERSION + "\0").encode("utf-16le")
    data[value_pos:value_pos + len(numeric)] = replacement + b"\0" * (len(numeric) - len(replacement))

    path.write_bytes(data)
    print("Patched Windows version resource:")
    print("  FileDescription = HSBridge V010")
    print("  ProductName = HSBridge")
    print("  FileVersion = 0.0.10.0")
    print("  ProductVersion = V010")
    print("  InternalName = HSBridge")
    print("  OriginalFilename = HSBridge.exe")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
