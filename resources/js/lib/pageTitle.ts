const MAX_TITLE_LENGTH = 60;

/**
 * Appends the app name unless the title already has it or the result would
 * exceed what search engines show (~60 characters), which crawlers flag as
 * "title too long".
 */
export function pageTitle(title: string | undefined, appName: string): string {
    if (!title) {
        return appName;
    }

    const withSuffix = `${title} - ${appName}`;

    return title.includes(appName) || withSuffix.length > MAX_TITLE_LENGTH ? title : withSuffix;
}
