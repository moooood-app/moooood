import textstat
from factories import ServiceFactory
from typing import Union, List, Dict, Any
import os

class ComplexityProcessor:
    def process(self, entry: str) -> Union[Dict[str, Any], List[Any]]:
        metrics = {
            # The measure of the readability of a text based on the
            # average number of syllables per word and words per sentence.
            # Higher values indicate more complex language
            # ----------------------------------------------
            # Minimum Value: 0 (easiest)
            # Maximum Value: 100 (most difficult)
            "flesch_kincaid_grade_level": textstat.flesch_kincaid_grade(entry),
            # A score that indicates how easy or difficult a text is to read.
            # Higher scores indicate easier readability, while lower scores indicate more complex text.
            # ----------------------------------------------
            # Minimum Value: 0 (most difficult)
            # Maximum Value: 100 (easiest)
            "flesch_reading_ease": textstat.flesch_reading_ease(entry),
            # The Gunning Fog Index is a readability formula that estimates the years of formal education
            # required to understand a piece of text. It considers the average number of words per sentence
            # and the percentage of complex words (words with three or more syllables) in the text.
            # ----------------------------------------------
            # Minimum Value: 0 (easiest)
            # Maximum Value: No upper limit, but typically ranges from 6 to 20+
            "gunning_fog_index": textstat.gunning_fog(entry),
            # The SMOG Index (Simple Measure of Gobbledygook) is another readability formula
            # that estimates the years of education needed to understand a text.
            # It calculates the index based on the number of complex words (words with three
            # or more syllables) in a sample of text.
            # ----------------------------------------------
            # Minimum Value: 0 (easiest)
            # Maximum Value: No upper limit, but typically ranges from 6 to 20+
            "smog_index": textstat.smog_index(entry),
            # Another readability index similar to Flesch-Kincaid Grade Level,
            # estimating the years of education needed to understand the text.
            # Minimum Value: 0 (easiest)
            # Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)
            "automated_readability_index": textstat.automated_readability_index(entry),
            # A readability index that calculates the grade level required
            # to understand the text based on characters per word and sentences per 100 words.
            # ----------------------------------------------
            # Minimum Value: -3 (rarely used in practice)
            # Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)
            "coleman_liau_index": textstat.coleman_liau_index(entry),
            # The Linsear Write Formula is a readability formula that estimates the readability
            # of a text based on the number of simple and complex words in a sample.
            # It considers words with one or two syllables as simple words and words with three
            #  or more syllables as complex words.
            # ----------------------------------------------
            # Minimum Value: 0 (easiest)
            # Maximum Value: Typically ranges from 0 to 20+
            "linsear_write_formula": textstat.linsear_write_formula(entry),
            # The Dale-Chall Readability Score is a readability formula that estimates the readability
            # of a text by considering a list of "easy" words. The formula calculates the percentage
            # of words in a text that are not on the list of easy words.
            # ----------------------------------------------
            # Minimum Value: 0 (easiest)
            # Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)
            "dale_chall_readability_score": textstat.dale_chall_readability_score(entry),
            # The Readability Consensus score is an average of several readability formulas,
            # including Flesch Reading Ease, Flesch-Kincaid Grade Level, Coleman-Liau Index,
            # Automated Readability Index, and Dale-Chall Readability Score.
            # This composite score provides an overall assessment of text readability,
            # considering multiple factors.
            # ----------------------------------------------
            # Minimum Value: 0 (most difficult)
            # Maximum Value: Typically ranges from 0 to 100, with higher values indicating easier readability.
            "readability_consensus": textstat.text_standard(
                entry, float_output=True
            ),
        }

        weights = {
            "flesch_kincaid_grade_level": 0.2,
            "flesch_reading_ease": 0.2,
            "gunning_fog_index": 0.1,
            "smog_index": 0.1,
            "automated_readability_index": 0.1,
            "coleman_liau_index": 0.1,
            "linsear_write_formula": 0.1,
            "dale_chall_readability_score": 0.1,
            "readability_consensus": 0.1,
        }

        # Normalize metrics based on observed practical ranges and invert if needed
        normalized = {
            # Inverting scores where lower values mean harder text
            "flesch_kincaid_grade_level": max(
                0, min(100, metrics["flesch_kincaid_grade_level"] * 5)
            ),
            "flesch_reading_ease": max(
                0, min(100, 100 + metrics["flesch_reading_ease"])
            ),  # Adjust negative scores
            "gunning_fog_index": max(0, min(100, metrics["gunning_fog_index"] * 5)),
            "smog_index": max(
                0, min(100, metrics["smog_index"] * 5)
            ),  # Address unusual smog_index of 0
            "automated_readability_index": max(
                0, min(100, metrics["automated_readability_index"] * 5)
            ),
            "coleman_liau_index": max(0, min(100, metrics["coleman_liau_index"] * 5)),
            "linsear_write_formula": max(
                0, min(100, metrics["linsear_write_formula"] * 5)
            ),
            "dale_chall_readability_score": max(
                0, min(100, metrics["dale_chall_readability_score"] * 5)
            ),
            "readability_consensus": max(
                0, min(100, metrics["readability_consensus"] * 5)
            ),
        }

        metrics["complexity_rating"] = round(sum(normalized[key] * weights[key] for key in weights), 2)

        return metrics

    def calculate_complexity_rating(self, metrics):
        # Define weights for each metric (adjusted for importance)
        weights = {
            "flesch_kincaid_grade_level": 0.2,
            "flesch_reading_ease": 0.2,
            "gunning_fog_index": 0.1,
            "smog_index": 0.1,
            "automated_readability_index": 0.1,
            "coleman_liau_index": 0.1,
            "linsear_write_formula": 0.1,
            "dale_chall_readability_score": 0.1,
            "readability_consensus": 0.1,
        }

        # Normalize metrics based on observed practical ranges and invert if needed
        normalized = {
            # Inverting scores where lower values mean harder text
            "flesch_kincaid_grade_level": max(
                0, min(100, metrics["flesch_kincaid_grade_level"] * 5)
            ),
            "flesch_reading_ease": max(
                0, min(100, 100 + metrics["flesch_reading_ease"])
            ),  # Adjust negative scores
            "gunning_fog_index": max(0, min(100, metrics["gunning_fog_index"] * 5)),
            "smog_index": max(
                0, min(100, metrics["smog_index"] * 5)
            ),  # Address unusual smog_index of 0
            "automated_readability_index": max(
                0, min(100, metrics["automated_readability_index"] * 5)
            ),
            "coleman_liau_index": max(0, min(100, metrics["coleman_liau_index"] * 5)),
            "linsear_write_formula": max(
                0, min(100, metrics["linsear_write_formula"] * 5)
            ),
            "dale_chall_readability_score": max(
                0, min(100, metrics["dale_chall_readability_score"] * 5)
            ),
            "readability_consensus": max(
                0, min(100, metrics["readability_consensus"] * 5)
            ),
        }
        return round(sum(normalized[key] * weights[key] for key in weights), 2)

def main():
    ServiceFactory.create(ComplexityProcessor(), os.getenv("COMPLEXITY_PROCESSOR_SQS_QUEUE")).handle()

if __name__ == "__main__":
    main()
