# Verification Toolchain

This toolchain consists of tools for verifying the various attestations about a package. Given a package with FAIR-formatted meta, the toolchain will generate metadata for use in assigning a trust score, including evaluation of whether a package (or release) should be accepted for FAIR federation and aggregation. The same tools are run on the package, regardless of its origin. Verification tools are run to verify attestations against external sourcees.

## Verification Tools

### DID & Domain Verification
- Verify DID Document
- Verify DNS for domain alias, if provided
- Domain reputation
- Check RBLs

### CVE Checks
- Check published CVE lists for package

### Label Checks
- Third-Party labels for the package

### Provenance Attestation Checks
- Check for VDP
- TBD for CRA Compliance

