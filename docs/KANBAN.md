# KANBAN board (assignment Step 3)

Create a GitHub **Project** (Projects → New project → *Board* template) on this repository, or an Azure DevOps board.
Use the columns **Backlog → To do → In progress → In review → Done**. Add the cards below
(GitHub: New issue for each, then add to the project) and move them across as the team works, so the board's history shows real progress.
Take a screenshot of the board for your submission.

| Card | Area | Column when finished |
|---|---|---|
| Design relational schema (7 tables) | Database | Done |
| Seed 10+ realistic rows per table | Database | Done |
| API: register / login with bcrypt | API | Done |
| API: Google sign-in (ID token check) | API | Done |
| API: profile + change password | API | Done |
| API: shop, checkout (transaction, stock check) | API | Done |
| API: admin endpoints | API | Done |
| Website: Google sign-in button | Website | Done |
| Android: login / register screens | App | Done |
| Android: Google sign-in (Credential Manager) | App | Done |
| Android: shop, cart, checkout, orders | App | Done |
| Android: Settings (profile + password + server) | App | Done |
| Android: Help screens | App | Done |
| Input validation + crash prevention | App | Done |
| Unit tests (Kotlin + PHP API + database) | Testing | Done |
| GitHub Actions pipeline (build on every push) | DevOps | Done |
| Demo rehearsal on the tablet | Delivery | To do |

Optional: with the GitHub CLI you can create them all at once, e.g.
`gh issue create --title "API: Google sign-in" --body "Verify Google ID token server-side"`.
