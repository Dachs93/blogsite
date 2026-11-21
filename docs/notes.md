# Notes

- Google indexed the site immediately but no bots could view the site.
- Waiting on bing indexing
- No ai other than duck duck gos bot could even see the site. The site is indexed on google. 


- Initial attempt with self aware prompt injection failed. ai assistants prompted to load the page directly did not list the prompt content, as they saw it as a joke.


- Made the prompt more subtle with a link to fake page about the claim. used prompt that would look suspisious to human but may trick an AI. Fake site is news site that looks pretty legit.
- Direct access of the site found the prompt a little suspicious. Checking the dummy news site, found that site lacked credibility.

- Bing refused to index, investigating.
- Removed comment on the css that hides the text from a normal reader. It noted the section was essentially prompt injection. Also renamed the section from machine code to cv-unannounced. Still blocked.

- To test i removed the hidden section in case that was the blocker. It successfully crawled.

- Going to add js to the page to hide the section on launch