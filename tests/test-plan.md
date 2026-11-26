# Test Plan (SRS §11) — results must be recorded after execution
| ID | Scenario | Steps | Expected | Actual | Status |
|---|---|---|---|---|---|
| TC-01 | Login valid | admin / David12@! | Admin dashboard | _not executed_ | Pending |
| TC-02 | Wrong password | admin / wrong | Rejected, no session | _not executed_ | Pending |
| TC-03 | Duplicate email register | meter MT-004, email jean@waterbill.test | Rejected | _not executed_ | Pending |
| TC-04 | Create customer | Customers > Register | Saved and listed | _not executed_ | Pending |
| TC-05 | Duplicate meter | Register with MT-001 | Validation error | _not executed_ | Pending |
| TC-06 | Valid reading | MT-001 value 1040 | Saved with reader ID | _not executed_ | Pending |
| TC-07 | Lower reading | MT-001 value 900 | Rejected | _not executed_ | Pending |
| TC-08 | Generate bill | MT-001, month 2025-06 after a reading | usage x rate + fixed + tax; linked to reading | _not executed_ | Pending |
| TC-09 | Duplicate bill | Generate 2025-05 for MT-001 | "bill already exists" | _not executed_ | Pending |
| TC-10 | Partial payment | Pay 10000 on bill 3 | Status Partially Paid, balance updated | _not executed_ | Pending |
| TC-11 | Other customer's bill | jean.paul opens bills/show&id=2 | 404, no data | _not executed_ | Pending |
| TC-12 | Missing CSRF | POST without token | HTTP 419, no change | _not executed_ | Pending |
| TC-13 | Search API (staff) | api/search&q=mar | JSON results | _not executed_ | Pending |
| TC-14 | Search API (customer/anon) | same URL | 403 / 401 | _not executed_ | Pending |
| TC-15 | XSS in name | `<script>alert(1)</script>` | Rendered as text | _not executed_ | Pending |
| TC-16 | Phone viewport | 375px width | Menu collapses, no overflow | _not executed_ | Pending |
